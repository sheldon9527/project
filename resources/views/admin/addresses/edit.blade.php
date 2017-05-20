@extends('admin.common.layout')

@section('content')
<div class="row">
    <div class="col-md-12">
        <form method="post" action="{{route('admin.teach.addresses.update',$address->id)}}"  enctype="multipart/form-data" class="form-horizontal">
            @include('admin.common.errors')
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <i class="fa fa-info"></i>
                    <h3 class="box-title">基本信息</h3>
                </div>

                <div class="box-body">
                    <table class="table table-hover table-striped">
                        <input type="hidden" name="_method" value="PUT" />
                        <tbody>
                        <tr>
                            <td style="font-size:16px;"><stong>目的地名称:</stong><B>{{$address->name}}</B></td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;"><stong>目的地状态:</stong>
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
                            <td style="font-size:16px;"><stong>目的地分类:</stong>
                                <select name="category_id" id="category_id" class="form-control">
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}" @if($address->category->id == $category->id)  selected = "selected" @endif>{{$category['name']}}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;"><stong>目的地地址:</stong>
                                <input type="text" class="form-control" name="address" value="{{trim($address->address)}}" required/>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;"><stong>联系方式:</stong>
                                <input type="text" class="form-control" name="telephone" value="{{trim($address->telephone)}}" required/>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;font-family:verdana;line-height:30px;"><stong>目的地介绍:</stong>
                                </br>
                                <textarea rows="6" cols="55" name="description">{{$address->description}}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;font-family:verdana;line-height:30px;"><stong>研学特色:</stong>
                            </br>
                            <textarea rows="4" cols="55" name="special">{{$address->special}}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:16px;">标签:
                                <div class="form-group">
                                    <div class="brand-wrap col-md-10 row">
                                        @if (isset($address->tags))
                                            @foreach($address->tags as $key => $tag)
                                            <div class="brand-item form-group">
                                                <div class="col-md-5">
                                                    <input type="text" name="tags[]" value="{{$tag->name}}" class="form-control" placeholder="标签">
                                                </div>
                                                <div class="col-md-2">
                                                    <a class="btn btn-danger pull-right del-brand">删除</a>
                                                </div>
                                            </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <a class="btn btn-warning add-brand {{isset($address->tags) ? 'col-md-offset-4' : ''}}"><i class="fa fa-plus"></i></a>
                                </div>

                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button class="btn btn-info" style="margin-right:60px;" type="submit">保存</button>
                                <a href="{{route('admin.teach.addresses.edit',[$address->id])}}">
                                    <span class="btn btn-info"  style="margin-right:60px;">取消</span>
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
                    <div class="box-title">地图位置:
                    </div>
                        <span class="badge bg-aqua" data-target="#myModal" data-toggle="modal" id="createMap">修改地图地址</span>
                    <div class="modal-body" id="map_main_two" style="height:350px;"></div>
                </div>
                <div style="display:none;">
                    <input class="form-control" hiddened="hiddened" id="map" type="text" value="{{$address->longitude}},{{$address->latitude}}" name="longlat"/>
                 </div>
                 <!--模态框（Modal） -->
                  <div aria-hidden="true" aria-labelledby="myModalLabel" class="modal fade" id="myModal" role="dialog" tabindex="-1">
                      <div class="modal-dialog">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                                      ×
                                  </button>
                                  <h4 class="modal-title" id="myModalLabel">
                                      百度地图选点(鼠标滚动缩放地图,鼠标拖动移动地图)
                                  </h4>
                              </div>
                              <div class="modal-body" id="map_main" style="height:400px;">
                              </div>
                              <div class="modal-footer">
                                  <input class="form-control" id="map_txt" readonly="" style="width:300px;float:left;" type="text" value=""/>
                                  <button class="btn btn-primary" onclick="setMapValue()" type="button">设置为此地址</button>
                                  <button class="btn btn-default" data-dismiss="modal" type="button">关闭</button>
                              </div>
                          </div>
                      </div>
                  </div>
            </div>
            <div>
                <div class="box box-info">
                    <div class="box-header">
                        <i class="fa fa-info">图片:</i>
                    <div class="padding-md">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                 @include('admin.common.attachment-dropzone', ['zoneType' => 'attachments', 'type' => 'teachAddress', 'tag' => 'detail'])
                            </div>
                        </div>
                    </div>
            </div>
            </div>
            </div>
        </div>
    </form>
</div>

<script type="text" id="brand-tpl">
    <div class="brand-item form-group">
        <div class="col-md-5">
            <input type="text" name="tags[]" value="" class="form-control" placeholder="标签">
        </div>
        <div class="col-md-2">
            <a class="btn btn-danger pull-left del-brand">删除</a>
        </div>
    </div>
</script>
<script src="http://api.map.baidu.com/api?v=2.0&ak=2d12993ce41407db4050140fe342d9ba" type="text/javascript"></script>
<script type="text/javascript">
require(['jquery'], function($) {
    // 标签添加
    var $brandWrap = $('div.brand-wrap');
    $('a.add-brand').click(function() {
        var num = $('div.brand-item').length;
        if (num >= 5) {
            alert('只能添加5个')
            return;
        }
        // 加个样式给添加按钮
        $(this).addClass('col-md-offset-7');
        var brandTpl = $('#brand-tpl').html();
        var par = $brandWrap.append(brandTpl);
    });
    // 删除
    $('div.brand-wrap').on('click', 'a.del-brand', function() {
        $(this).closest('.brand-item').remove();
        if ($('div.brand-item').length == 0) {
            $('a.add-brand').removeClass('col-md-offset-7');
        }
    });


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

    $(function(){
      $("#createMap").click(function(){
          setTimeout(function() {     //添加延时加载。解决问题
              var map = new BMap.Map("map_main");
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
                  map.addEventListener("click", function(e){
                      var lng_lat = e.point.lng+','+e.point.lat;
                      $("#map_txt").val(lng_lat);                 //加入到设置框
                      var point = new BMap.Point(e.point.lng,e.point.lat);
                      var marker = new BMap.Marker(point);        // 创建标注
                      map.clearOverlays();
                      map.addOverlay(marker);
                  });
              });
          },300);
      });
  });

 });
 //设置经纬度
function setMapValue(){
  if($("#map_txt").val()==""){ alert('你还没选择相应的坐标点^_^哦'); return false; }
  $("#map").val($("#map_txt").val());
  $('#myModal').modal('hide')
}
 </script>
@endsection
