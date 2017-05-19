<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\SampleOrderRecord\StoreRequest;
use App\Http\Requests\Api\SampleOrderRecord\UpdateRequest;
use App\Models\SampleOrderRecord;
use Carbon\Carbon;

class SampleOrderRecordController extends BaseController
{
    /**
     * @apiGroup  sample_order_records
     * @apiDescription 创建打样单记录
     *
     * @api {post} /sample/orders/{id}/records 创建打样单记录
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String}  track_name  快递公司
     * @apiParam {String}  track_number  物流单号
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 201
     */
    public function store($id, StoreRequest $request)
    {
        $user = \Auth::user();
        $order = $user->sampleOrders()->find($id);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        if ($order->checkOwner(\Auth::user()) || $order->status != 'IN_PROGRESS' || $order->submit_count >= 3) {
            return $this->response->errorForbidden();
        }

        $record = new SampleOrderRecord();

        $record->track_name = $request->get('track_name');
        $record->track_number = $request->get('track_number');
        $record->sample_order_id = $order->id;
        $record->status = 'PENDING_CONFIRM';
        $record->type = 'maker';

        $record->save();

        $order->submit_count = $order->submit_count + 1;

        $order->status = 'SUBMITTED';

        $order->save();

        return $this->response->created();
    }

    public function update($orderId, $id, UpdateRequest $request)
    {
        $user = \Auth::user();
        $order = $user->sampleOrders()->find($orderId);

        if (!$order) {
            return $this->response->errorNotFound();
        }

        if ($order->checkContact(\Auth::user()) || $order->status != 'DEFARA_SUBMITTED' || $order->submit_count > 3) {
            return $this->response->errorForbidden();
        }

        $operate = $request->get('operate');

        $method = camel_case($operate);

        return $this->$method($order, $id);
    }

    /**
     * @apiGroup  sample_order_records
     * @apiDescription 打样单记录拒绝
     *
     * @api {put} /sample/orders/{orderId}/records/{id}--(reject) 打样单记录拒绝
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='reject'} operate  打样单记录拒绝
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function reject($order, $id)
    {
        $record = $order->records()->where('type', 'defara')->find($id);
        if (!$record) {
            return $this->response->errorNotFound();
        }
        $record->status = 'DISSATISFIED';
        $record->confirmed_at = Carbon::now();
        $record->save();

        $recordParent = SampleOrderRecord::find($record->parent_id);
        $recordParent->status = 'DISSATISFIED';
        $recordParent->confirmed_at = Carbon::now();
        $recordParent->save();

        $order->status = 'IN_PROGRESS';
        $order->save();

        return $this->response->noContent();
    }

    /**
     * @apiGroup  sample_order_records
     * @apiDescription 打样单记录接受
     *
     * @api {put} /sample/orders/{orderId}/records/{id}--(finish) 打样单记录接受
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='finish'} operate  打样单记录接受
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    private function finish($order, $id)
    {
        $record = $order->records()->find($id);
        if (!$record) {
            return $this->response->errorNotFound();
        }

        $record->status = 'SATISFIED';
        $record->confirmed_at = Carbon::now();
        $record->save();

        $recordParent = SampleOrderRecord::find($record->parent_id);

        \DB::beginTransaction();
        try {
            $recordParent->status = 'SATISFIED';
            $recordParent->confirmed_at = Carbon::now();
            $recordParent->save();

            $order->status = 'FINISHED';
            $order->settleUp()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }
    /**
     * @apiGroup  sample_order_records
     * @apiDescription 打样单记录不满意
     *
     * @api {put} /sample/orders/{orderId}/records/{id}--(unsatisity) 打样单记录不满意
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='unsatisity'} operate  打样单记录不满意
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204
     */
    public function unsatisity($order, $id)
    {
        $record = $order->records()->where('type', 'defara')->find($id);
        if (!$record) {
            return $this->response->errorNotFound();
        }

        $record->status = 'DISSATISFIED';
        $record->confirmed_at = Carbon::now();
        $record->save();

        $recordParent = SampleOrderRecord::find($record->parent_id);
        \DB::beginTransaction();
        try {
            $recordParent->status = 'DISSATISFIED';
            $recordParent->confirmed_at = Carbon::now();
            $recordParent->save();

            $order->status = 'UNSATISTY';
            $order->settleUp()->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response->errorInternal();
        }

        return $this->response->noContent();
    }
}
