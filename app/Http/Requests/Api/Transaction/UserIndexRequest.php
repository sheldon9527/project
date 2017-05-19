<?php

namespace App\Http\Requests\Api\Transaction;

use App\Http\Requests\Api\Request;

class UserIndexRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time' => 'date',
            'end_time' => 'date',
        ];
    }
}
