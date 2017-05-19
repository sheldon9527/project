<?php
namespace App\Http\Requests\Admin\Category;

use App\Http\Requests\Admin\Request;

class StoreRequest extends Request
{
    public function rules()
    {
        return [
            'parent_id' => 'integer|exists:categories,id',
            'name'      => 'required|string',
            'icon_url'  => 'image',
        ];
    }
}
