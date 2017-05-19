<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function userIndex(User $user)
    {
        return $this->isDesigner($user);
    }

    public function store(User $user)
    {
        return $this->isDesigner($user);
    }

    public function update(User $user, Service $service)
    {
        return $this->isOwner($user, $service);
    }

    public function destroy(User $user, Service $service)
    {
        return $this->isOwner($user, $service);
    }

    protected function isOwner($user, $service)
    {
        return $user->id === $service->user_id;
    }

    protected function isDesigner($user)
    {
        return $user->type == 'DESIGNER';
    }
}
