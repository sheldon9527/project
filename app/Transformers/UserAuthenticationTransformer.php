<?php

namespace App\Transformers;

use App\Models\User;
use App\Models\Attachment;
use App\Models\UserAuthentication;
use League\Fractal\TransformerAbstract;

class UserAuthenticationTransformer extends TransformerAbstract
{
    // 可以返回的信息
    protected $availableIncludes = [
        'user',
    ];

    public function transform(UserAuthentication $authentication)
    {
        foreach ($authentication->info as $key => $info) {
            $authentication->$key = $info;
        }

        unset($authentication->info);
        $authentication->avatar = url($authentication->avatar);

        if (isset($authentication['services'])) {
            $services = $authentication['services'];

            foreach ($services as $key => &$service) {
                if (isset($service['works'])) {
                    foreach ((array)$service['works'] as $workKey => $work) {
                        $attachment = Attachment::find($work['id']);

                        if ($attachment) {
                            $service['works'][$workKey]['name'] = $attachment->filename;
                            $service['works'][$workKey]['url'] = $attachment->url ?: url($attachment->relative_path);
                        } else {
                            $service['works'][$workKey]['name'] = '';
                            $service['works'][$workKey]['url'] = '';
                        }
                    }
                }
            }

            $authentication['services'] = $services;
        }

        return $authentication->attributesToArray();
    }

    public function includeUser(UserAuthentication $authentication)
    {
        return $this->item($authentication->user, new UserTransformer());
    }
}