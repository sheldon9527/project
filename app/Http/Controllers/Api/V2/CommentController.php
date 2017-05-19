<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Comment\StoreRequest;
use App\Models\InquiryOrder;
use App\Models\SampleOrder;
use App\Models\OrderComment;
use App\Models\ProductionOrder;
use App\Models\InquiryServiceOrder;

class CommentController extends BaseController
{
    /**
     * @apiGroup order_comments
     * @apiDescription  订单评论
     *
     * @api {post} /order/comments 订单评论
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {String='inquiry_service'} order_type inquiry_service 委托服务询价
     * @apiParam {interger} order_id
     * @apiParam {String} content
     * HTTP/1.1 200 OK
     *{
     *}
     */
    public function store(StoreRequest $request)
    {
        $user = \Auth::user();

        $id = $request->get('order_id');
        switch ($type = $request->get('order_type')) {
            case 'inquiry':
                $object = InquiryOrder::find($id);
                break;
            case 'inquiry_service':
                $object = InquiryServiceOrder::find($id);
                break;
            case 'sample':
                $object = SampleOrder::find($id);
                break;
            case 'production':
                $object = ProductionOrder::find($id);
                break;
            default:
                return $this->response->errorNotFound();
                break;
        }

        $comment = new OrderComment();
        $comment->user_id = $user->id;
        $comment->content = $request->get('content');
        $comment->commentable()->associate($object);
        $comment->save();
        if ($attachments = $request->get('attachments')) {
            $comment->updateAttachment($attachments, 'detail');
        }

        return $this->response->noContent();
    }
}
