<?php

namespace App\Http\Requests\Api\Download;

use App\Http\Requests\Api\Request;

class DownloadRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attachment_ids' => 'required|array|min:1',
        ];
    }
}
