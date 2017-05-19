<?php

namespace App\Http\Requests\Api\AppealOrder;

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
            'operate' => 'required|in:cancel,return,apply',
        ];
        $operate = $this->request->get('operate');

        switch ($operate) {
            case 'apply':
                $rules['description'] = 'required|string';
                $rules['attachments'] = 'array';
                break;
        }

        return $rules;
    }
}
