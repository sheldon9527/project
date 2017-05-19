@extends('admin.common.layout') @section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box-header">
            <h3 class="box-title">管理员详情</h3>
        </div>
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <table class="table table-hover table-striped">
                    <tbody>
                        <tr>
                            <td>id</td>
                            <td>{{$admin->id}}</td>
                        </tr>
                        <tr>
                            <td>登陆账号</td>
                            <td>{{$admin->username}}</td>
                        </tr>
                        <tr>
                            <td>手机</td>
                            <td>{{$admin->cellphone}}</td>
                        </tr>
                        <tr>
                            <td>邮箱</td>
                            <td>{{$admin->email}}</td>
                        </tr>
                        <tr>
                            <td>状态</td>
                            <td>@if ($admin->status == 'ACTIVE')激活@else 未激活@endif</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="{{route('admin.admins.edit',['id' => $admin->id])}}">
                                    <button class="btn btn-primary" type="submit">编辑</button>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
