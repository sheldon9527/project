<?php

namespace App\Http\Requests\Api\ProductionOrder;

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
        $rules = [
            'operate' => 'required|in:ship,accept,cancel',
        ];
        $operate = $this->request->get('operate');

        switch ($operate) {
            case 'ship':
                $rules['track_name'] = 'required|string';
                $rules['track_number'] = 'required|string';
                $rules['invoice'] = 'required';
                $rules['pack_order'] = 'required';
                break;
        }

        return $rules;
    }
}
