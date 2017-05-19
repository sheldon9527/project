<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Bank;

class BankTransformer extends TransformerAbstract
{
    public function transform(Bank $bank)
    {
        return $bank->attributesToArray();
    }
}
