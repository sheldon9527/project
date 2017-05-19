<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Pay\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use PayPal;
use Illuminate\Http\Request;
use App\Traits\OrderPay;

class PaymentController extends BaseController
{
    use OrderPay;
    /**
     * @apiGroup Payment
     * @apiDescription 付款
     *
     * @api {post} /order/pay 付款
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String} order_no 订单编号
     * @apiParam {String='defara','alipay','paypal'} pay_mode 支付方式
     * @apiParam {String} success_return_url 支付成功后跳转的地址
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 302 or 204 跳转到第三方 或者直接付款成功
     */
    public function pay(PaymentRequest $request)
    {
        $user = \Auth::user();

        $orderNo = $request->get('order_no');
        $mode = $request->get('pay_mode');
        $order = Order::findByNo($orderNo);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        // 如果订单价格为0  暂时forbidden
        if ($order->getAmount() <= 0) {
            return $this->response->errorForbidden();
        }

        if ($mode == 'defara' && $user->amount < $order->getAmount()) {
            $this->response->errorForbidden(trans('error.orders.insufficient_balance'));
        }
        // 这里默认所有订单未付款为PENDING_PAY
        // 可以用$order->checkPayStatus()之类的方法处理
        $orderShortType = substr($orderNo, 0, 2);

        if ($order->status != 'PENDING_PAY' && $orderShortType != 'PD') {
            return $this->response->errorForbidden();
        }

        // 付款成功的跳转地址

        $successReturnUrl = $request->get('success_return_url');
        \Cache::store('database')->put('payment-success-'.$order->order_no, $successReturnUrl, 30);

        $payMode = $mode.'Pay';

        return $this->$payMode($order, $request);
    }

    /**
     * 本地付款.
     *
     * @param [object] $order 订单对象
     *
     * @return http response
     */
    protected function defaraPay($order, $request)
    {
        $user = \Auth::user();

        if (!\Auth::validate(['id' => $user->id, 'password' => $request->get('password')])) {
            return $this->response->errorForbidden(trans('error.auth.invalid_password'));
        }

        \DB::beginTransaction();

        try {
            $amount = $order->getAmount();

            $transaction = new Transaction();
            $transaction->user()->associate($user);
            $transaction->pay_type = 'DEFARA';
            $transaction->amount = -$amount;
            $transaction->type = strtoupper($order->getType());
            $transaction->transactionable()->associate($order);
            $transaction->save();

            $user->amount -= $amount;
            $user->save();

            $order->paid()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }

    public function alipayReturn(Request $request)
    {
        $gateway = \Omnipay::gateway('alipay');
        $options = [
            'request_params' => $request->input(),
        ];

        $response = $gateway->completePurchase($options)->send();

        if ($response->isPaid()) {

            // TODO 这块写的不好
            $outTradeNo = $request->get('out_trade_no');
            $paymentId = explode('-', $outTradeNo)[1];
            $payment = Payment::find($paymentId);
            $payAccount = $request->get('buyer_email');

            $order = $payment->paymentable;

            $successReturnUrl = \Cache::store('database')->pull('payment-success-'.$order->order_no) ?: '/';

            return redirect($successReturnUrl);
        } else {
            //支付失败通知.
            return redirect(config('domain.pay_fail'));
        }
    }

    public function alipayNotify(Request $request)
    {
        \Log::debug('=====> start notify');
        $gateway = \Omnipay::gateway('alipay');
        $options = [
            'request_params' => $request->input(),
        ];

        $response = $gateway->completePurchase($options)->send();

        if ($response->isPaid() && $this->alipaySuccess($request)) {
            return 'success';
            \Log::debug('=====> notify success');
        } else {
            //支付失败通知.
            return 'fail';
            \Log::debug('=====> notify fail');
        }
    }

    //TODO 没时间封装，以后再说
    protected function alipaySuccess($request)
    {
        \Log::debug('=====> notify setp 1');
        $tradeNo = $request->get('trade_no');
        $outTradeNo = $request->get('out_trade_no');
        $paymentId = explode('-', $outTradeNo)[1];
        $payment = Payment::find($paymentId);
        $payAccount = $request->get('buyer_email');

        $order = $payment->paymentable;

        if ($order->status != 'PENDING_PAY' && $order->status != 'IN_PROGRESS') {
            return true;
        }

        \DB::beginTransaction();
        try {
            \Log::debug('=====> notify setp 2');
            $user = $payment->user;
            $orderType = $order->getType();

            $transaction = new Transaction();
            $transaction->user()->associate($user);
            $transaction->pay_type = 'ALIPAY';
            $transaction->amount = -($payment->amount);
            // 充值是正的
            $transaction->amount = ($orderType == 'recharge') ? $payment->amount : -($payment->amount);
            $transaction->transactionable()->associate($order);
            $transaction->type = strtoupper($orderType);
            $transaction->save();

            $payment->transaction()->associate($transaction);
            $payment->remote_no = $tradeNo;
            $payment->pay_account = $payAccount;
            $payment->status = 'PAID';
            $payment->save();

            // 充值
            if ($orderType == 'recharge') {
                $user->amount += $payment->amount;
                $user->save();
            }

            // 将订单设置为paid
            $order->paid()->save();

            \Log::debug('=====> notify setp commit');
            \DB::commit();

            return true;
        } catch (\Exception $e) {
            \DB::rollBack();
        }

        return false;
    }

    public function paypalReturn(Request $request)
    {
        $id = $request->get('paymentId');
        $token = $request->get('token');
        $payer_id = $request->get('PayerID');

        $payment = PayPal::getById($id, $this->getPaypalContext());

        try {
            $paymentExecution = PayPal::PaymentExecution();
            $paymentExecution->setPayerId($payer_id);
            $executePayment = $payment->execute($paymentExecution, $this->getPaypalContext());
        } catch (\Exception $e) {
            return redirect(config('domain.pay_fail'));
        }

        if ($executePayment->state == 'approved') {
            // 这个地方除非到这了服务器挂了
            // 不然不应该走到rollback
            // 为了防止这种意外，可能需要有脚本处理
            \DB::beginTransaction();

            try {
                $payAccount = @$executePayment->payer->payer_info->email ?: '';
                $paymentId = @$executePayment->id;

                $paymentModel = Payment::where('remote_no', $paymentId)->first();

                $order = $paymentModel->paymentable;
                $orderType = $order->getType();

                $user = $paymentModel->user;
                $transaction = new Transaction();
                $transaction->user()->associate($user);
                $transaction->pay_type = 'PAYPAL';

                // 充值是正的
                $transaction->amount = ($orderType == 'recharge') ? $paymentModel->amount : -($paymentModel->amount);
                $transaction->transactionable()->associate($order);
                $transaction->type = strtoupper($orderType);
                $transaction->save();

                $paymentModel->transaction()->associate($transaction);
                $paymentModel->pay_account = $payAccount;
                $paymentModel->status = 'PAID';
                $paymentModel->save();

                // 充值
                if ($orderType == 'recharge') {
                    $user->amount += $paymentModel->amount;
                    $user->save();
                }

                // 将订单设置为paid
                $order->paid()->save();

                \DB::commit();

                $successReturnUrl = \Cache::store('database')->pull('payment-success-'.$order->order_no) ?: '/';

                return redirect($successReturnUrl);
            } catch (\Exception $e) {
                \DB::rollBack();
            }
        }

        return redirect(config('domain.pay_fail'));
    }

    // 是否一定需要cancel？TODO 没时间研究
    public function paypalCancel()
    {
        return;
    }
}
