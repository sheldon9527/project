<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Admin\StoreRequest;
use App\Http\Requests\Admin\Admin\UpdateRequest;
use App\Models\Admin;

class AdminController extends BaseController
{
    public function index()
    {
        $admins = Admin::query();

        $searchColumns = ['name', 'cellphone', 'status'];

        if ($name = $this->request->get('name')) {
            $admins->where('first_name', 'like', '%' . $name . '%')
                ->orWhere('last_name', 'like', '%' . $name . '%');
        }

        if ($cellphone = $this->request->get('cellphone')) {
            $admins->where('cellphone', 'LIKE', "%$cellphone%");
        }

        if ($status = $this->request->get('status')) {
            $admins->where('status', $status);
        }

        $admins = $admins->orderBy('id', 'desc')->paginate();

        $params = array_filter($this->request->all());

        $admins->appends($params);

        return view('admin.company.index', compact('admins', 'searchColumns'));
    }

    public function store(StoreRequest $request)
    {
        if ($username = $request->get('username')) {
            $exist = Admin::where('username', $username)->exists();
            if ($exist) {
                return redirect(route('admin.admins.index'))->withErrors(['登陆账号已经存在']);
            }
        }

        $admin            = new Admin();
        $admin->username  = $request->get('username');
        $admin->email     = $request->get('email');
        $admin->cellphone = $request->get('cellphone');
        $admin->password  = bcrypt(trim($request->get('password')));
        $admin->status    = $request->get('status');
        $admin->save();

        return redirect(route('admin.admins.index'));
    }

    public function edit($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            abort(404);
        }

        return view('admin.company.edit', compact('admin'));
    }

    public function update($id, UpdateRequest $request)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            abort(404);
        }

        if ($username = $request->get('username') && $admin->isDirty('username')) {
            $exist = Admin::where('username', $username)->exists();
            if ($exist) {
                return redirect(route('admin.admins.edit', $id))->withErrors(['登陆账号已经存在']);
            }
        }

        $admin->username  = $request->get('username');
        $admin->email     = $request->get('email');
        $admin->cellphone = $request->get('cellphone');

        if ($password = $request->get('password')) {
            $admin->password = bcrypt(trim($request->get('password')));
        }

        $admin->status = $request->get('status');
        $admin->save();

        return redirect(route('admin.admins.show', $id));
    }

    public function show($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            abort(404);
        }

        return view('admin.company.show', compact('admin'));
    }

    public function destroy($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            abort(404);
        }

        if ($admin->isSuper) {
            abort(403);
        }

        $admin->delete();
        return redirect(route('admin.admins.index'));
    }
}
