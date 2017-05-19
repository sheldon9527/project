<?php

namespace App\Transformers;

use App\Models\User;

class MakerTransformer extends UserTransformer
{
    protected $availableIncludes = [
        'categories',
        'factory',
        'profile',
        'socials',
    ];

    public function includeFactory(User $user)
    {
        return $this->item($user->factory, new FactoryTransformer());
    }
}
