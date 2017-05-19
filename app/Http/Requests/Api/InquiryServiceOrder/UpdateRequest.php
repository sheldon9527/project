<?php

namespace App\Http\Requests\Api\InquiryServiceOrder;

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
            'operate' => 'required|in:match,draft_update,final_update,match_cancel,draft_submit,draft_confirm,draft_cancel,fanal_submit,accept',
        ];
    }
}
