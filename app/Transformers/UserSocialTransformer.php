<?php

namespace App\Transformers;

use App\Models\UserSocial;
use League\Fractal\TransformerAbstract;

class UserSocialTransformer extends TransformerAbstract
{
    // 默认的transform，返回对象的所有信息
    public function transform(UserSocial $userSocial)
    {
        return $userSocial->attributesToArray();
    }
}
