@extends('admin.common.layout')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <i class="fa fa-info"></i>
                    <h3 class="box-title">基本信息</h3>
                </div>
                <div class="box-body">
                    <table class="table table-hover table-striped">
                        <tbody>
                        <tr>
                            <td style="font-size:16px;"><strong>目的地名称:</strong><B>{{$address->name}}</B></td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;"><strong>目的地状态:</strong>
                                @if($address->status == 'ACTIVE')
                                    <span class="badge bg-green">状态:已上架</span>
                                @endif
                                @if(in_array($address->status,['INACTIVE','APPROVALED']))
                                    <span class="badge bg-red">状态:未上架</span>
                                @endif
                                @if($address->status == 'NO_APPROVAL')
                                    <span class="badge bg-yellow">状态:未审批</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;"><strong>目的地分类:</strong>{{$address->category->name}}</td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;"><strong>目的地地址:</strong>{{$address->address}}</td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;font-family:verdana;line-height:30px;"><strong>目的地介绍:</strong></br>
                            {{$address->description}}</td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;font-family:verdana;line-height:30px;"><strong>研学特色:</strong></br>
                            {{$address->special}}</td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;font-family:verdana;line-height:30px;"><strong>标签:</strong></br>
                                @foreach($address->tags as $tag)
                                    <a class="badge bg-aqua" id='link'>{{$tag->name}}</a>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="{{route('admin.teach.addresses.edit',[$address->id])}}">
                                    <button class="btn btn-info" style="margin-right:60px;" type="submit">编辑</button>
                                </a>
                                <a href="">
                                    <button class="btn btn-info"  style="margin-right:60px;" type="submit">下线</button>
                                </a>
                                <a href="">
                                    <button class="btn btn-info"  style="margin-right:60px;" type="submit">删除</button>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header">
                    <i class="fa fa-info"></i>
                    <div class="box-title">地图位置:</div>
                    <div class="modal-body" id="map_main_two" style="height:350px;">第一次显示</div>
                </div>
                <div style="display:none;">
                    <input class="form-control" hiddened="hiddened" id="map" type="text" value="{{$address->longitude}},{{$address->latitude}}"/>
                 </div>
            </div>
            <div>
                <div class="box box-info">
                    <div class="box-header">
                        <i class="fa fa-info">图片:</i>
                        <div class="padding-md">
                            @if ($address->attachments)
                                @foreach ($address->attachments as $attachment)
                                <div class="col-sm-6 col-md-3">
                                    <div class="thumbnail">
                                      <img src="{{url($attachment->relative_path)}}" alt="...">
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
<script src="http://api.map.baidu.com/api?v=2.0&ak=2d12993ce41407db4050140fe342d9ba" type="text/javascript"></script>
<script type="text/javascript">
    var map = new BMap.Map("map_main_two");
        var myCity = new BMap.LocalCity();
        myCity.get(function(res){
            map.centerAndZoom(res.center,res.level);
            var old_map = $('#map').val();      //如果已设置过
            if(old_map.length > '5'){            //打开的时候显示已设置的
                $("#map_txt").val(old_map);
                var oldMap = old_map.split(",");
                var point = new BMap.Point(oldMap[0],oldMap[1]);
                var marker = new BMap.Marker(point);        // 创建标注
                map.clearOverlays();
                map.addOverlay(marker);
            }
        });
        </script>
@endsection
