<?php

namespace App\Transformers;

use App\Models\User;

class RecommendDesignerTransformer extends DesignerTransformer
{
    public function transform(User $user)
    {
        // 这里单独处理, 可以放在user的extra缓存中
        $position = $user->profile->position;
        if ($position) {
            $user->position_name = $position->name;
            if (app()->getLocale() == 'en') {
                $user->position_name = $position->en_name;
            }
        }

        if ($user->avatar) {
            $user->avatar = $user->getClourUrl($user->avatar);
        }

        $result = $user->attributesToArray();

        return $result;
    }
}
