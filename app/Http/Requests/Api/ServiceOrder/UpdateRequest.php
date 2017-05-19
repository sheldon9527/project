<?php

namespace App\Http\Requests\Api\ServiceOrder;

use App\Http\Requests\Api\Request;

class UpdateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'operate' => 'required|in:cancel,draft_submit,confirm,submit,accept,deliver',
        ];
    }
}