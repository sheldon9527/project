<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use App\Models\DesignerWork;

class RecommendTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['works','makers'];

    public function transform($object)
    {
        return $object->attributesToArray();
    }

    public function includeMakers()
    {
        $makers = User::leftJoin('recommendations', 'recommendations.recommendable_id', '=', 'users.id')
            ->where('recommendations.recommendable_type', 'App\Models\User')
            ->where('recommendations.type', 'MAKER')
            ->select('users.*', 'recommendations.recommend_picture_url')
            ->limit(6)
            ->get();

        return $this->collection($makers, new RecommendMakerTransformer());
    }

    public function includeDesigners()
    {
        $designers = User::leftJoin('recommendations', 'recommendations.recommendable_id', '=', 'users.id')
            ->where('recommendations.recommendable_type', 'App\Models\User')
            ->where('recommendations.type', 'DESIGNER')
            ->select('users.*', 'recommendations.recommend_picture_url')
            ->limit(6)
            ->get();

        return $this->collection($designers, new RecommendDesignerTransformer());
    }

    public function includeWorks()
    {
        $works = DesignerWork::leftJoin('recommendations', 'recommendations.recommendable_id', '=', 'designer_works.id')
            ->where('recommendations.recommendable_type', 'App\Models\DesignerWork')
            ->where('recommendations.type', 'WORK')
            ->select('designer_works.*', 'recommendations.recommend_picture_url')
            ->orderBy('recommendations.weight','desc')
            ->limit(6)
            ->get();

        return $this->collection($works, new RecommendWorkTransformer());
    }
}
