<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Api\Attachment\StoreRequest;
use App\Http\Requests\Api\Attachment\UpdateRequest;
use App\Http\Requests\Api\Download\DownloadRequest;
use App\Transformers\AttachmentTransformer;
use App\Jobs\DealWithAttachment;
use App\Models\Attachment;
use App\Models\Factory;
use App\Models\Service;

class AttachmentController extends BaseController
{
    /**
     * @apiGroup attachment
     * @apiDescription  附件上传
     *
     * @api {post} /attachments 附件上传
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Boolean} [is_async] 上传类型 是否异步
     * @apiParam {File} attachment 附件
     * @apiParam {String='sample_order_records','service_order','inquiry_order','appeal_orders','sample_order','appeal_orders_intervene','purchase_order','work','service','factory'} type 资源类型
     * @apiParam {String='detail','first','final','cover','owner','contact'} [tag] 附件标签 cover 封面 service的tag 包含 detail 详情, first 初稿, final 终稿
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 OK
     *{
     *  "data": {
     *    "relative_path": "assets/service/2016/03/16/bba92d08362147055741020d5d3ca50f5ce52fc7.png",
     *    "filename": "QQ%E6%88%AA%E5%9B%BE20151118173832.png",
     *    "tag": null,
     *    "mime_types": "image/png",
     *    "updated_at": "2016-03-16 07:12:46",
     *    "created_at": "2016-03-16 07:12:46",
     *    "id": 1828
     *  }
     *}
     */
    public function store(StoreRequest $request)
    {
        $attachment = new Attachment();

        //upload attachment
        $file = \Input::file('attachment');
        //save path
        $type = camel_case($request->get('type'));
        $destinationPath = 'assets/'.$type.'/'.date('Y/m/d');
        // upload attachment deal
        $extension = $file->getClientOriginalExtension(); // getting image extension
        $originFileName = $file->getClientOriginalName();
        $fileName = $this->_getRandName($extension); // renameing image
        $mimeTypes = $file->getClientMimeType();

        $path = (string) $file->move($destinationPath, $fileName);

        $data = [
            'relative_path' => $path,
            'filename' => $originFileName,
            'tag' => trim($request->get('tag')) ?: null,
            'mime_types' => $mimeTypes,
        ];

        $attachment->fill($data);

        $attachment->user_id = \Auth::id();

        // 暂时全是同步上传到七牛
        if (!$attachment->sync()) {
            return $this->response->errorInternal();
        }

        $attachment->save();

        return $this->response->item($attachment, new AttachmentTransformer());
    }

    private function _getRandName($extension = null)
    {
        $rand = hash('ripemd160', time().rand(1000000, 99999999));

        if ($extension) {
            $rand .= '.'.$extension;
        }

        return $rand;
    }

    /**
     * @apiGroup attachment
     * @apiDescription 附件编辑
     *
     * @api {put} /attachments/{id} 附件编辑
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Integer} attachment_id 附件id
     * @apiParam {String} filename 附件名称
     * @apiParam {String} [description] 附件描述
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 200 updated
     *{
     *  "data": {
     *    "relative_path": "assets/service/2016/03/16/bba92d08362147055741020d5d3ca50f5ce52fc7.png",
     *    "filename": "QQ%E6%88%AA%E5%9B%BE20151118173832.png",
     *    "tag": null,
     *    "mime_types": "image/png",
     *    "updated_at": "2016-03-16 07:12:46",
     *    "created_at": "2016-03-16 07:12:46",
     *    "id": 1828
     *  }
     *}
     */
    public function update($id, UpdateRequest $request)
    {
        $user = \Auth::user();

        $attachment = $user->attachments()->find($id);

        if (!$attachment) {
            return $this->response->errorNotFound();
        }

        $attachment->filename = $request->get('filename');
        $attachment->description = $request->get('description');

        $attachment->save();

        return $this->response->item($attachment, new AttachmentTransformer());
    }

    /**
     * @apiGroup attachment
     * @apiDescription 附件删除
     *
     * @api {delete} /attachments/{id} 附件删除
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 204 deleted
     */
    public function destroy($id)
    {
        $user = \Auth::user();

        $attachment = $user->attachments()->find($id);

        if (!$attachment) {
            return $this->response->errorNotFound();
        }

        $attachment->delete();

        return $this->response->noContent();
    }

    /**
     * @apiGroup attachment
     * @apiDescription 批量下载
     *
     * @api {post} /attachments/download 批量下载
     * @apiVersion 0.2.0
     * @apiPermission jwt
     * @apiParam {Array} attachment_ids 附件的id，如果只填了一个，是下载单个文件，填多个会压缩为zip包
     * @apiSuccessExample {json} Success-Response:
     * HTTP/1.1 download response
     */
    public function download(DownloadRequest $request)
    {
        $user = \Auth::user();

        $attachmentIds = $request->get('attachment_ids');

        $attachments = Attachment::whereIn('id', $attachmentIds)->get();

        if (!$attachments->count()) {
            return $this->response->errorForbidden();
        }

        if ($attachments->count() == 1) {
            $attachment = $attachments->first();

            if (!$attachment->allowDownload($user)) {
                return $this->response->errorForbidden();
            }

            return response()->download(public_path($attachment->relative_path));
        } else {
            $zip = new \ZipArchive();
            $zipFile = storage_path('app/cache/'.date('Ymdhis').uniqid().'.zip');

            $zip->open($zipFile, \ZIPARCHIVE::CREATE);

            foreach ($attachments as $attachment) {
                if (!$attachment->allowDownload($user)) {
                    continue;
                }

                $filename = basename($attachment->relative_path);

                $zip->addFile($attachment->relative_path, $filename);
            }
            $zip->close();

            return response()->download($zipFile);
        }
    }
}
