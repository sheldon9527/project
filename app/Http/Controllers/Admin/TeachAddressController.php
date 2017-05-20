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
            if ($status == 'INACTIVE') {
                $addresses->whereIn('status', ['INACTIVE', 'APPROVALED']);
            } else {
                $addresses->where('status', $status);
            }
        }
        $addresses     = $addresses->orderBy('id', 'desc')->paginate();
        $searchColumns = [
            'name'   => $name,
            'type'   => $type,
            'status' => $status,
        ];
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
        $bool                      = $teachAddress->save();
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
        $address = TeachAddress::withTrashed()->find($id);
        if (!$address) {
            abort(404);
        }

        return view('admin.addresses.show', compact('address'));
    }
    /**
     * [destory 删除]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function destory($id)
    {
        $address = TeachAddress::withTrashed()->find($id);
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
        $address = TeachAddress::withTrashed()->find($id);
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
    /**
     * [multiDestory 批量删除]
     * @return [type] [description]
     */
    public function multiDestory()
    {
        $teachAddressIds = $this->request->get('teachAddressIds');
        $type            = $this->request->get('type');
        if (!$teachAddressIds) {
            abort(404);
        }
        $teachAddressIdsArray = explode(',', $teachAddressIds);
        if (empty($teachAddressIdsArray)) {
            abort(404);
        }
        $route = 'admin.teach.addresses.recycle.index';
        foreach ($teachAddressIdsArray as $teachAddressId) {
            $address = TeachAddress::withTrashed()->find($teachAddressId);
            if (!$address) {
                continue;
            }
            switch ($type) {
                case 'delete':
                    $address->delete();
                    $route = 'admin.teach.addresses.index';
                    break;
                case 'approval':
                    $address->delete();
                    $route = 'admin.teach.addresses.approval.index';
                    break;
                case 'complete':
                    $address->forceDelete();
                    break;
                case 'recycle':
                    $address->restore();
                    break;
            }
        }

        return redirect(route($route));
    }
    /**
     * [multiUpdate 批量审批]
     * @return [type] [description]
     */
    public function multiUpdate()
    {
        $teachAddressIds = $this->request->get('teachAddressIds');
        $type            = $this->request->get('type');
        if (!$teachAddressIds) {
            abort(404);
        }
        $teachAddressIdsArray = explode(',', $teachAddressIds);
        if (empty($teachAddressIdsArray)) {
            abort(404);
        }
        foreach ($teachAddressIdsArray as $teachAddressId) {
            $address = TeachAddress::find($teachAddressId);
            if (!$address) {
                continue;
            }
            switch ($type) {
                case 'approval':
                    $address->status = 'APPROVALED';
                    $address->save();
                    $route = 'admin.teach.addresses.approval.index';
                    break;
            }
        }

        return redirect(route($route));
    }
    /**
     * [recycleIndex 回收站目的地管理]
     * @return [type] [description]
     */
    public function recycleIndex()
    {
        $addresses = TeachAddress::onlyTrashed();
        if ($name = $this->request->get('name')) {
            $addresses->where('name', 'like', '%' . $name . '%');
        }
        if ($type = $this->request->get('type')) {
            $addresses->where('type', $type);
        }
        if ($status = $this->request->get('status')) {
            if ($status == 'INACTIVE') {
                $addresses->whereIn('status', ['INACTIVE', 'APPROVALED']);
            } else {
                $addresses->where('status', $status);
            }
        }
        $addresses     = $addresses->orderBy('id', 'desc')->paginate();
        $searchColumns = [
            'name'   => $name,
            'type'   => $type,
            'status' => $status,
        ];

        return view('admin.addresses.recycleIndex', compact('addresses', 'searchColumns'));
    }
    /**
     * [recycleIndex 目的地审批]
     * @return [type] [description]
     */
    public function approvalIndex()
    {
        $addresses = TeachAddress::query();
        if ($name = $this->request->get('name')) {
            $addresses->where('name', 'like', '%' . $name . '%');
        }
        if ($type = $this->request->get('type')) {
            $addresses->where('type', $type);
        }
        $addresses     = $addresses->where('status', 'NO_APPROVAL')->orderBy('id', 'desc')->paginate();
        $searchColumns = [
            'name' => $name,
            'type' => $type,
        ];

        return view('admin.addresses.approvalIndex', compact('addresses', 'searchColumns'));
    }
}
