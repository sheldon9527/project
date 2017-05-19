<?php

namespace App\ViewComposers;

use Illuminate\View\View;
use App\Services\Admin;

class AdminComposer
{
    protected $user;

    public function __construct(Admin $admin)
    {
        $this->user = $admin->user();
    }

    public function compose(View $view)
    {
        $view->with('admin', $this->user);
    }
}
