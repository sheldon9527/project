<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\DesignerWork;
use League\Fractal\ParamBag;

class WorkTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['attachments', 'more', 'user'];

    protected $loginUser;

    public function __construct($loginUser = null)
    {
        $this->loginUser = $loginUser;
    }

    public function transform(DesignerWork $work)
    {
        if ($work->cover_picture_url) {
            $work->cover_picture_url = $work->getCloudUrl($work->cover_picture_url);
        }

        if ($work->recommend_picture_url) {
            $work->recommend_picture_url = $work->getCloudUrl($work->recommend_picture_url);
        }

        $work->is_favorite;

        return $work->attributesToArray();
    }

    public function includeUser(DesignerWork $work)
    {
        $user = $work->user;

        if ($this->loginUser) {
            $followers = $user->followedByCurrentUser()->where('user_favorites.user_id', $this->loginUser->id)->first();

            $user->setRelation('followedByCurrentUser', $followers);
        }

        return $this->item($user, new UserTransformer());
    }

    public function includeAttachments(DesignerWork $work)
    {
        $attachments = $work->attachments()
            ->where('tag', 'detail')
            ->get();

        // 对详情图片进行排序
        $extra = $work->extra;
        if (isset($extra['attachment_ids']) && $sortIds = $extra['attachment_ids']) {
            $attachments = $attachments->sortBy(function ($attachment, $key) use ($sortIds) {
                return array_search($attachment->id, $sortIds);
            });
        }

        return $this->collection($attachments, new AttachmentTransformer());
    }

    // 给作品详情使用
    public function includeMore(DesignerWork $work, ParamBag $params = null)
    {
        $limit = 4;
        if ($params) {
            $limit = (array) $params->get('limit');
            $limit = (int) current($limit);
        }

        $works = $work->user->works()->where('designer_works.id', '!=', $work->id)->limit($limit)->get();

        return $this->collection($works, new self());
    }
}
