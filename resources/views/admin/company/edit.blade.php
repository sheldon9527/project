@extends('admin.common.layout') @section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box-header">
            <h3 class="box-title">管理员编辑</h3>
        </div>
        <div class="ibox float-e-margins">
            @include('admin.common.errors')
            <div class="ibox-content">
                <form method="post" action="{{route('admin.admins.update', [$admin->id])}}" id="form" enctype="multipart/form-data" class="form-horizontal">
                    <input type="hidden" name="_method" value="PUT" />
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><span class="text-red">*</span>登陆账号:</label>
                        <div class="col-sm-3">
                            <input type="text" name="username" value="{{$admin->username}}" class="form-control" required="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">手机:</label>
                        <div class="col-sm-3">
                            <input type="text" name="cellphone" value="{{$admin->cellphone}}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">邮箱:</label>
                        <div class="col-sm-3">
                            <input type="text" name="email" value="{{$admin->email}}" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><span class="text-red">*</span>密码:</label>
                        <div class="col-sm-3">
                            <input type="text" name="password" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><span class="text-red">*</span>状态:</label>
                        <div class="col-sm-3">
                            <div>
                                <label>
                                    <input class="page-settings-all-radio" @if ($admin->status == 'ACTIVE') checked="checked" @endif name="status" value="ACTIVE" type="radio">
                                    <span i18n-content="optionAllPages">激活&nbsp;&nbsp;</span>
                                </label>
                            </div>
                            <div>
                                <label>
                                    <input class="page-settings-all-radio" @if ($admin->status == 'INACTIVE') checked="checked" @endif name="status" value="INACTIVE" type="radio">
                                    <span i18n-content="optionAllPages">未激活</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
