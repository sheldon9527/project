<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Contract;

class ContractTransformer extends TransformerAbstract
{
    public function transform(Contract $contract)
    {
        return $contract->attributesToArray();
    }
}
