<?php

namespace App\Transformers;

use App\Models\Service;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'category',
        'user',
        'more',
        'attachments',
    ];

    protected $loginUser;

    public function __construct($loginUser = null)
    {
        $this->loginUser = $loginUser;
    }

    public function transform(Service $service)
    {
        if ($service->cover_picture_url) {
            $service->cover_picture_url = $service->getCloudUrl($service->cover_picture_url);
        }

        if ($service->recommend_picture_url) {
            $service->recommend_picture_url = $service->getCloudUrl($service->recommend_picture_url);
        }

        $service->is_favorite;

        return $service->attributesToArray();
    }

    public function includeCategory(Service $service)
    {
        if ($service->category) {
            return $this->item($service->category, new CategoryTransformer());
        }
    }

    public function includeUser(Service $service)
    {
        $user = $service->user;

        if ($this->loginUser) {
            $followers = $user->followedByCurrentUser()->where('user_favorites.user_id', $this->loginUser->id)->first();

            $user->setRelation('followedByCurrentUser', $followers);
        }

        return $this->item($user, new UserTransformer());
    }

    // 给服务订单详情使用
    public function includeMore(Service $service, ParamBag $params = null)
    {
        $limit = 4;
        if ($params) {
            $limit = (array) $params->get('limit');
            $limit = (int) current($limit);
        }

        $services = $service->user->services()->where('services.id', '!=', $service->id)->limit($limit)->get();

        return $this->collection($services, new self());
    }

    public function includeAttachments(Service $service)
    {
        $attachment = $service->attachments;
        if ($attachment) {
            return $this->collection($attachment, new AttachmentTransformer());
        }

        return;
    }
}
