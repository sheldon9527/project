<?php

namespace App\Http\Requests\Api\Contract;

use App\Http\Requests\Api\Request;

class PreviewRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|in:service,inquiry,sample,purchase,production',
        ];
    }
}
