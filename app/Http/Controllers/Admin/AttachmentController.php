<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attachment;

class AttachmentController extends BaseController
{
    /**
     * [store 上传附件图片]
     * @return [type] [description]
     */
    public function store()
    {
        $type         = $this->request->get('type');
        $image        = $this->request->file('file');
        $originalName = $image->getClientOriginalName();
        $extension    = $image->getClientOriginalExtension();
        $mimeType     = $image->getClientMimeType();
        $filename     = mt_rand() . uniqid() . '.' . $extension;

        $filePath = (string) $image->move('assets/' . $type . date('/y/m/'), $filename);

        $attachment                = new Attachment();
        $attachment->relative_path = $filePath;
        $attachment->filename      = $originalName;
        $attachment->tag           = $this->request->get('tag');
        $attachment->mime_types    = $mimeType;
        $attachment->save();

        return response()->json($attachment);
    }
}
