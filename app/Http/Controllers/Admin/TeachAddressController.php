<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TeachAddress\StoreRequest;
use App\Http\Requests\Admin\TeachAddress\UpdateRequest;
use App\Models\Category;
use App\Models\Tags;
use App\Models\TeachAddress;
use Geohash\Geohash;

class TeachAddressController extends BaseController
{
    /**
     * [index 列表]
     * @return [type] [description]
     */
    public function index()
    {
        $addresses     = TeachAddress::query();
        $searchColumns = ['name', 'type', 'status'];
        if ($name = $this->request->get('name')) {
            $addresses->where('name', 'like', '%' . $name . '%');
        }
        if ($type = $this->request->get('type')) {
            $addresses->where('type', $type);
        }
        if ($status = $this->request->get('status')) {
            $addresses->where('status', $status);
        }
        $addresses = $addresses->orderBy('id', 'desc')->paginate();

        return view('admin.addresses.index', compact('addresses', 'searchColumns'));
    }
    /**
     * [create 添加页面]
     * @return [type] [description]
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)->get();

        return view('admin.addresses.create', compact('categories'));
    }
    /**
     * [store 创建目的地]
     * @return [type] [description]
     */
    public function store(StoreRequest $request)
    {
        $teachAddress              = new TeachAddress();
        $teachAddress->name        = $request->get('name');
        $teachAddress->category_id = $request->get('category_id');
        $teachAddress->telephone   = $request->get('telephone');
        $teachAddress->address     = $request->get('address');
        $teachAddress->type        = 'IN';
        $bool = $teachAddress->save();
        if ($bool) {
            return redirect(route('admin.teach.addresses.edit', $teachAddress->id));
        }
    }
    /**
     * [show 目的地详情]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function show($id)
    {
        $address = TeachAddress::find($id);
        if (!$address) {
            abort(404);
        }

        // dd($address->tags);

        return view('admin.addresses.show', compact('address'));
    }
    /**
     * [destory 删除]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function destory($id)
    {
        $address = TeachAddress::find($id);
        if (!$address) {
            abort(404);
        }
        $address->delete();

        return redirect(route('admin.teach.addresses.index'));
    }
    /**
     * [edit 更新页面]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function edit($id)
    {
        $address = TeachAddress::find($id);
        if (!$address) {
            abort(404);
        }
        $categories        = Category::where('parent_id', 0)->get();
        $detailAttachments = $address->attachments;

        return view('admin.addresses.edit', compact('address', 'categories', 'detailAttachments'));
    }
    /**
     * [update 更新]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function update($id, UpdateRequest $request)
    {
        $address = TeachAddress::find($id);
        if (!$address) {
            abort(404);
        }
        $address->fill($request->input());
        if ($longlatArray = explode(',', $request->get('longlat'))) {
            $address->longitude = $longlatArray[0];
            $address->latitude  = $longlatArray[1];
            $address->geohash   = Geohash::encode($longlatArray[1], $longlatArray[0]);
        }

        if (array_key_exists('tags', $request->input())) {
            $tags = $request->get('tags');
            $ids  = $address->tags;
            if ($ids) {
                foreach ($ids as $tagId) {
                    Tags::where('id', $tagId->id)->delete();
                }
            }
            foreach ($tags as $key => $tag) {
                $newTag                   = new Tags();
                $newTag->teach_address_id = $id;
                $newTag->name             = $tag;
                $newTag->save();
            }
        }
        if (array_key_exists('attachments', $request->input())) {
            $address->updateAttachment($request->get('attachments'), 'detail');
        }
        $address->save();

        return redirect(route('admin.teach.addresses.show', $id));
    }
}
