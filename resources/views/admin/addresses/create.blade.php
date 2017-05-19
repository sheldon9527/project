@extends('admin.common.layout')

@section('content')
    <div class="row">
        <div class="box-header">
            <h3 class="box-title">目的地创建</h3>
        </div>
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <form method="post" action="{{route('admin.teach.addresses.store')}}" id="form" enctype="multipart/form-data" class="form-horizontal">
                        @include('admin.common.errors')
                        <div class="form-group">
                            <label class="col-sm-2 control-label">
                                <span class="text-red">*</span>输入目的地名称:
                            </label>
                            <div class="col-sm-3">
                                <input type="text" name="name" value="{{old('name')}}" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">
                                <span class="text-red">*</span>选择目的地分类:
                            </label>
                            <div class="col-sm-3">
                                <select name="category_id" id="category_id" class="form-control">
                                    @foreach($categories as $category)
                                        <option value="{{$category['id']}}">{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-sm-2 control-label"><span class="text-red">*</span>输入目的地地址:</label>
                            <div class="col-sm-3"><input type="text" name="telephone" value="{{old('telephone')}}" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-sm-2 control-label"><span class="text-red">*</span>输入目的地电话:</label>
                            <div class="col-sm-3">
                                <input type="text" name="address" value="{{old('address')}}" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-2">
                                <button class="btn btn-info" type="submit">确认,继续编辑详情</button>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                 <a href="{{route('admin.teach.addresses.index')}}" class="btn btn-info">取消</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
