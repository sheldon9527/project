<?php

namespace App\Http\Requests\Api\PurchaseOrder;

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
            'operate' => 'required|in:cancel,confirm,finish,reply',
        ];
        $operate = $this->request->get('operate');

        switch ($operate) {
            case 'reply':
                $rules['production_duration'] = 'required|integer';
                $rules['production_price'] = 'required|numeric';
                $rules['contact_note'] = 'string';
                break;
        }

        return $rules;
    }
}
