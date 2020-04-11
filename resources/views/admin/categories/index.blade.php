@extends('admin.common.layout')

@section('content')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">目的地分类列表</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                @include('admin.common.errors')
                <a data-toggle="modal" data-target="#addRootModal" class="pull-right btn btn-info" type="button">添加分类</a>
            </div>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-sm-12">
                <table id="category-table" class="table table-bordered table-striped text-center">
                    <thead>
                    <tr>
                        <th>分类id</th>
                        <th>分类名称</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($roots as $root)
                            <tr class="root info" data-root-id={{$root->id}}>
                                <td>
                                    <a class="h3 text-danger">
                                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                                    </a> &nbsp;&nbsp;&nbsp;&nbsp;{{$root->id}}
                                </td>
                                <td>{{$root->name}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">操作
                                        <span class="fa fa-caret-down"></span></button>
                                        <ul class="dropdown-menu slim-menu">
                                            <li><a class="edit-node"
                                                data-toggle="modal"
                                                data-target="#editNodeModal"
                                                data-href="{{route('admin.categories.update', $root->id)}}"
                                                data-node-name="{{$root->name}}"
                                                data-node-icon="{{$root->icon_url}}"
                                                data-node-en-name="{{$root->en_name}}">修改</a></li>
                                            <li><a href="{{route('admin.categories.destroy', $root->id)}}" data-method="delete" data-confirm="确定删除主分类吗?">删除</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<!-- Modal -->
<div class="modal fade" id="addRootModal" tabindex="-1" role="dialog" aria-labelledby="addRootModal">
    <form action="{{route('admin.categories.store')}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">添加分类</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">名称:</label>
                        <div class="col-sm-8">
                            <input type="text" name="name" value="" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Create</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal -->
<div class="modal fade" id="addChildModal" tabindex="-1" role="dialog" aria-labelledby="addChildModal">
    <form action="{{route('admin.categories.store')}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
        <input type="hidden" name="parent_id" value="0">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">添加 <span class="root-name text-info"></span> 的二级分类</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">名称:</label>
                        <div class="col-sm-8">
                            <input type="text" name="name" value="" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Create</button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- Modal -->
<div class="modal fade" id="editNodeModal" tabindex="-1" role="dialog" aria-labelledby="addRootModal">
    <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data" >
        <input type="hidden" name="_method" value="PUT" >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">修改分类</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">名称:</label>
                        <div class="col-sm-8">
                            <input type="text" name="name" value="" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Edit</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    require(['jquery'], function($) {

		// 隐藏，展开
        $('#category-table').on('click', 'tr[data-root-id] .fa', function() {
            var root_id = $(this).closest('tr').data('root-id');

            var $children = $('tr.child-'+root_id);
            if ($children.length) {
                if ($children.is(":hidden")) {
                    $(this).removeClass('fa-angle-right').addClass('fa-angle-down');
                    $children.show();
                }
                else {
                    $(this).removeClass('fa-angle-down').addClass('fa-angle-right');
                    $children.hide();
                }
            }
        });

		// 二级分类的排序
        $('#category-table').on('click', '.child a.sort', function() {

            var operate = $(this).data('operate');
            var $targetTr = $(this).closest('tr');

            if(operate =='up'){
                var $prev = $targetTr.prev('.child');
                if (!$prev.length) {
                    return;
                }
            } else {
                var $next = $targetTr.next('.child');
                if (!$next.length) {
                    return;
                }
            }

            $id = $(this).data('id');

            $.ajax({
                url: '/manager/categories/' + $id,
                type: 'POST',
                data:{
                    operate: operate,
                    _method:'PUT',
                }
            })
            .done(function (data) {
                if (data.success == 1) {
                    if(operate == 'up'){
                        $targetTr.remove().insertBefore($prev);
                    }else {
                        $targetTr.remove().insertAfter($next);
                    }
                }
            });
        });

        // 添加子分类
        $('#category-table').on('click', '.add-category-child', function () {
            var root_name = $(this).data('root-name');
            var root_id = $(this).data('root-id');

            var $model = $('#addChildModal');
            $model.find('.modal-title span.root-name').text(root_name);
            $model.find('input[name=parent_id]').val(root_id);
        });

        // 修改子分类
        $('#category-table').on('click', '.edit-node', function () {
            var node_name = $(this).data('node-name');
            var node_icon = $(this).data('node-icon');
            var href = $(this).data('href');

            var $model = $('#editNodeModal');

            $model.find('form').attr('action', href);
            $model.find('input[name=name]').val(node_name);
            $model.find('#avatar-preview').attr('src',node_icon);
        });
    });
</script>
@endsection
