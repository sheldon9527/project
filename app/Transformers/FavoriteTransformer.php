<?php

namespace App\Transformers;

use App\Models\DesignerWork;
use App\Models\Service;
use App\Models\User;
use App\Models\UserFavorite;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class FavoriteTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['user'];

    public function transform(UserFavorite $favorite)
    {
        $source = $favorite->favorable()->first();
        $key = strtolower($favorite->tag);
        if ($source instanceof User) {
            $this->setDefaultIncludes([$key]);
        }

        if ($source instanceof DesignerWork) {
            $this->setDefaultIncludes([$key]);
        }

        if ($source instanceof Service) {
            $this->setDefaultIncludes([$key]);
        }

        return $favorite->attributesToArray();
    }

    public function includeDesigner(UserFavorite $favorite, ParamBag $params = null)
    {
        return $this->item($favorite->favorable, new DesignerTransformer());
    }

    public function includeMaker(UserFavorite $favorite, ParamBag $params = null)
    {
        return $this->item($favorite->favorable, new MakerTransformer());
    }

    public function includeUser(UserFavorite $favorite, ParamBag $params = null)
    {
        return $this->item($favorite->user, new UserTransformer());
    }

    public function includeWork(UserFavorite $favorite, ParamBag $params = null)
    {
        return $this->item($favorite->work, new WorkTransformer());
    }

    public function includeService(UserFavorite $favorite, ParamBag $params = null)
    {
        return $this->item($favorite->service, new ServiceTransformer());
    }
}
