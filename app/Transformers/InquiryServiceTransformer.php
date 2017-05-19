<?php

namespace App\Transformers;

use App\Models\InquiryService;
use League\Fractal\TransformerAbstract;

class InquiryServiceTransformer extends TransformerAbstract
{
    // 可以返回的信息
    protected $availableIncludes = [
        'categories',
        'results',
        'attachments',
    ];

    public function transform(InquiryService $service)
    {
        $service->cover_picture_url = $service->cover_picture_url
            ? $service->getCloudUrl($service->cover_picture_url)
            : $service->getCloudUrl($service->category->icon_url);

        return $service->attributesToArray();
    }

    public function includeCategories(InquiryService $service)
    {
        return $this->collection($service->categories, new InquiryServiceCategoryTransformer());
    }

    public function includeResults(InquiryService $service)
    {
        return $this->collection($service->results, new ServiceResultTransformer());
    }

    public function includeAttachments(InquiryService $service)
    {
        return $this->collection($service->Attachments, new AttachmentTransformer());
    }
}
