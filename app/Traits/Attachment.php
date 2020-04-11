<?php

namespace App\Traits;

use App\Models\Attachment as AttachmentModel;

trait Attachment
{
    public function updateAttachment($attachments = [], $tag = null, $orderBy = null)
    {
        $attachmentIds = array_column((array) $attachments, 'id');
        $delAttachment = $this->attachments()->whereNotIn('id', $attachmentIds);

        if ($tag) {
            $delAttachment->where('tag', $tag);
        }

        $delAttachment->delete();

        $count = count($attachments);

        foreach ((array) $attachments as $key => $attachment) {
            $attachmentModel = AttachmentModel::find($attachment['id']);

            //TODO 不属于我的附件几乎不可能,暂不处理
            if ($attachmentModel->attachable_id && $attachmentModel->attachable_id != $this->id) {
                continue;
            }

            if (array_key_exists('filename', $attachment)) {
                $attachmentModel->filename = $attachment['filename'];
            }
            if (array_key_exists('description', $attachment)) {
                $attachmentModel->description = $attachment['description'];
            }

            $attachmentModel->attachable()->associate($this);
            $attachmentModel->user_id = $this->user_id;
            if ($orderBy) {
                $attachmentModel->weight = $count - $key;
            }
            if ($tag) {
                $attachmentModel->tag = $tag;
            }

            $attachmentModel->save();
        }

        return;
    }
}
