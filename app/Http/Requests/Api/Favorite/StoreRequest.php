<?php

namespace App\Http\Requests\Api\Favorite;

use App\Http\Requests\Api\Request;

class StoreRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|in:designer,maker,service,work,DESIGNER,MAKER,SERVICE,WORK',
            'type_id' => 'required|integer',
        ];
    }
}
