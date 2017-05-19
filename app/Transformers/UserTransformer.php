<?php

namespace App\Transformers;

use App\Models\Category;
use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    // 可以返回的信息
    protected $availableIncludes = [
        'profile',
        'categories',
        'socials',
    ];

    public function transform(User $user)
    {
        $user->is_favorite;
        if ($user->avatar) {
            $user->avatar = $user->getCloudUrl($user->avatar);
        } else {
            $user->avatar = url(config('image.defaultImg'));
        }

        if ($user->recommend_picture_url) {
            $user->recommend_picture_url = $user->getCloudUrl($user->recommend_picture_url);
        }
        if ($user->type == 'MAKER' && $user->avatar == '') {
            $user->avatar = $user->factory->cover_picture_url ? url($user->factory->cover_picture_url) : url(config('image.defaultImg'));
        }

        return $user->attributesToArray();
    }

    public function includeProfile(User $user)
    {
        if ($user->type == 'DESIGNER') {
            return $this->item($user->profile, new DesignerProfileTransformer());
        } elseif ($user->type == 'DEALER') {
            return $this->item($user->profile, new DealerProfileTransformer());
        }
    }

    // TODO 查询次数太多，需优化
    public function includeCategories(User $user)
    {
        $childrenIds = $user->categories()->where('parent_id', '!=', 0)->lists('parent_id', 'parent_id')->toArray();

        $parentIds = $user->categories()->where('parent_id', 0)->lists('categories.id')->toArray();

        $categoryIds = array_merge($childrenIds, $parentIds);

        $categories = Category::whereIn('categories.id', $categoryIds)->get();

        $ids = $user->categories()->lists('categories.id')->toArray();

        return $this->collection($categories, new UserCategoryTransformer($ids));
    }

    public function includeSocials(User $user)
    {
        $socials = $user->socials;

        if ($socials) {
            return $this->collection($socials, new UserSocialTransformer());
        }
    }

    //image convert base64 data
    private function _imageToData($path)
    {
        $url = url($path);
        $type = pathinfo($url, PATHINFO_EXTENSION);
        $data = file_get_contents($url);
        $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);

        return $base64;
    }
}
