<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\ParamBag;

class DesignerTransformer extends UserTransformer
{
    protected $loginUser;

    public function __construct($loginUser = null)
    {
        $this->loginUser = $loginUser;
    }

    // 可以返回的信息
    protected $availableIncludes = [
        'profile',
        'categories',
        'works',
        'services',
        'socials',
        'inquiryServices',
        'inquiryServiceAttachments',
        'styles',
    ];

    public function includeWorks(User $user, ParamBag $params = null)
    {
        $limit = null;
        if ($params) {
            $limit = (array) $params->get('limit');
            $limit = (int) current($limit);
        }

        $worksPaginate = $user->works()->active();
        $loginUser = $this->loginUser;
        //是否被登陆用户收藏
        if ($loginUser) {
            $worksPaginate->with(['followedByCurrentUser' => function ($query) use ($loginUser) {
                $query->where('user_favorites.user_id', $loginUser->id);
            }]);
        }

        $worksPaginate = $worksPaginate->orderBy('weight', 'desc')->paginate($limit);

        // 实现的不太对，再研究一下代码
        $adapter = app('\League\Fractal\Pagination\IlluminatePaginatorAdapter', [$worksPaginate]);
        $paginateInfo = app('League\Fractal\Serializer\ArraySerializer')->paginator($adapter);

        return $this->collection($worksPaginate, new WorkTransformer())->setMeta($paginateInfo);
    }

    public function includeServices(User $user, ParamBag $params = null)
    {
        $limit = null;
        if ($params) {
            $limit = (array) $params->get('limit');
            $limit = (int) current($limit);
        }

        $servicesPaginate = $user->services()->active();
        $loginUser = $this->loginUser;
        //是否被登陆用户收藏
        if ($loginUser) {
            $servicesPaginate->with(['followedByCurrentUser' => function ($query) use ($loginUser) {
                $query->where('user_favorites.user_id', $loginUser->id);
            }]);
        }
        $servicesPaginate = $servicesPaginate->paginate($limit);

        // 实现的不太对，再研究一下代码
        $adapter = app('\League\Fractal\Pagination\IlluminatePaginatorAdapter', [$servicesPaginate]);
        $paginateInfo = app('League\Fractal\Serializer\ArraySerializer')->paginator($adapter);

        return $this->collection($servicesPaginate, new ServiceTransformer())->setMeta($paginateInfo);
    }

    public function includeInquiryServices(User $user, ParamBag $params = null)
    {
        $services = $user->inquiryServices()->where('status', 'ACTIVE')->get();
        return $this->collection($services, new InquiryServiceTransformer());
    }

    public function includeStyles(User $user, ParamBag $params = null)
    {
        return $this->collection($user->styles, new StyleTransformer());
    }

    public function includeInquiryServiceAttachments(User $user, ParamBag $params = null)
    {
        $limit = 12;
        if ($params) {
            $limit = (array) $params->get('limit');
            $limit = (int) current($limit);
        }

        $attachments = $user->inquiryServiceAttachments()
            ->where('tag', 'inquiryService')
            ->orderBy('weight', 'DESC')
            ->paginate($limit);

        // 实现的不太对，再研究一下代码
        $adapter = app('\League\Fractal\Pagination\IlluminatePaginatorAdapter', [$attachments]);
        $paginateInfo = app('League\Fractal\Serializer\ArraySerializer')->paginator($adapter);

        return $this->collection($attachments, new AttachmentTransformer())->setMeta($paginateInfo);
    }
}
