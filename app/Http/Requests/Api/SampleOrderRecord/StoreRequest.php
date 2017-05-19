<?php

namespace App\Http\Requests\Api\SampleOrderRecord;

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
            'track_name' => 'required|string',
            'track_number' => 'required|string',
        ];
    }
}
