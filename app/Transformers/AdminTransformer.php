<?php

namespace App\Transformers;

use App\Models\Admin;
use League\Fractal\TransformerAbstract;

class AdminTransformer extends TransformerAbstract
{
    public function transform(Admin $admin)
    {
        return $admin->attributesToArray();
    }
}
