@extends('admin.common.layout') @section('content')

<div class="row">

    <div class="col-md-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">管理员列表</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default dropdown-toggle btn-search">筛选 <span class="caret"></span></button>
                            <div class="dropdown-menu dropdown-menu-right dropdown-search" role="menu">
                                <form class="form-horizontal" role="form">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">name</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="姓名" name="name" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">phone</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" placeholder="手机" name="cellphone" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">status</label>
                                        <div class="col-sm-10">
                                            <select name="status" class="selectpicker" data-width="auto">
                                                <option value="">--</option>
                                                <option value="ACTIVE">激活</option>
                                                <option value="INACTIVE">未激活</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary pull-right"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                                </form>
                            </div>
                        </div>

                        @include('admin.common.search-tips')
                        <button type="button" class="pull-right btn btn-info" data-toggle="modal" data-target="#clientModel">添加管理用户</button>
                    </div>
                </div>

                <div class="row">&nbsp;</div>
                <div class="row">
                    @include('admin.common.errors')
                    <div class="col-sm-12">
                        <table id="infos" class="table table-bordered table-striped text-center">
                            <thead>
                                <tr role="row">
                                    <td>用户id</td>
                                    <td>登陆账号</td>
                                    <td>联系电话</td>
                                    <td>邮箱</td>
                                    <td>状态</td>
                                    <td>操作</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($admins as $admin)
                                <tr role="row">
                                    <td>
                                        <a>{{$admin->id}}</a>
                                    </td>
                                    <td>{{$admin->username}}</td>
                                    <td>{{$admin->cellphone}}</td>
                                    <td>{{$admin->email}}</td>
                                    <td>{{$admin->statusLabel[$admin->status]}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">操作
                                                <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu slim-menu">
                                                <li><a href="{{route('admin.admins.edit', [$admin->id])}}">编辑</a></li>
                                                <li><a href="{{route('admin.admins.show', [$admin->id])}}">详情</a></li>
                                                <li><a data-method="DELETE" href="{{route('admin.admins.destroy', [$admin->id])}}">删除</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5"></div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers" id="example1_paginate">
                            {!! $admins->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="clientModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <form action="{{route('admin.admins.store')}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">添加管理用户</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><span class="text-red">*</span>登陆账号</label>
                                <div class="col-sm-8">
                                    <input type="text" name="username" value="" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">手机</label>
                                <div class="col-sm-8">
                                    <input type="text" name="cellphone" value="" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">邮箱</label>
                                <div class="col-sm-8">
                                    <input type="email" name="email" value="" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label"><span class="text-red">*</span>密码</label>
                                <div class="col-sm-8">
                                    <input type="text" name="password" value="" class="form-control" required="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><span class="text-red">*</span>确认密码</label>
                                <div class="col-sm-8">
                                    <input type="text" name="password_confirmation" value="" class="form-control" required="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label"><span class="text-red">*</span>状态</label>
                                <div class="col-sm-8">
                                    <select name="status" class="selectpicker" data-width="auto" required="">
                                        <option value="ACTIVE">激活</option>
                                        <option value="INACTIVE">未激活</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                            <button type="submit" class="btn btn-info">保存</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
