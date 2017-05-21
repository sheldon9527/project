<?php
namespace App\Transformers;

use App\Models\TeachAddress;
use App\Transformers\AttachmentTransformer;
use App\Transformers\CategoryTransformer;
use App\Transformers\TagTransformer;
use League\Fractal\TransformerAbstract;

class TeachAddressTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'category',
        'tags',
        'attachments',
    ];

    public function transform(TeachAddress $address)
    {
        return $address->attributesToArray();
    }

    public function includeCategory(TeachAddress $address)
    {
        $category = $address->category;
        if ($category) {
            return $this->item($category, new CategoryTransformer());
        }
    }

    public function includeTags(TeachAddress $address)
    {
        $tags = $address->tags;
        if ($tags) {
            return $this->collection($tags, new TagTransformer());
        }
    }

    public function includeAttachments(TeachAddress $address)
    {
        $attachments = $address->attachments;
        if ($attachments) {
            return $this->collection($attachments, new AttachmentTransformer());
        }
    }
}
