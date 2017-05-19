<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\Showroom;
use App\Transformers\ShowroomTransformer;

// showroom 这个东西目前没有用，showroom里面显示的是用户详情, 先放着
class ShowroomController extends BaseController
{
    public function someoneShow($id)
    {
        $user = User::find($id);

        if (!$user || $user->group->name != 'DESIGNER') {
            return $this->response->errorForbidden();
        }

        $showroom = $user->showrooms()->first();

        // 一般不会发生
        if (!$showroom) {
            return $this->response->errorInternal();
        }

        return $this->response->item($showroom, new ShowroomTransformer());
    }

    public function show($id)
    {
        $showroom = Showroom::find($id);

        if (!$user || $user->group->name != 'DESIGNER') {
            return $this->response->errorForbidden();
        }

        return $this->response->item($showroom, new ShowroomTransformer());
    }
}
