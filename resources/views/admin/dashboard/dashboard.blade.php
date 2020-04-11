@extends('admin.common.layout')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="col-md-6">
            <div class="box box-solid bg-teal-gradient">
                <div class="box-header">
                    <i class="fa fa-th"></i>
                    <h3 class="box-title">新增目的地统计</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn bg-teal btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn bg-teal btn-sm" data-widget="remove"><i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body border-radius-none">
                    <h4>总目的地: {{$statisticData['addresses']['total']}}</h4>
                    <div id="new-users"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-solid bg-blue-gradient">
                <div class="box-header">
                    <i class="fa fa-th"></i>
                    <h3 class="box-title">待审批目的地统计</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn bg-blue-gradient btn-sm" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn bg-blue-gradient btn-sm" data-widget="remove"><i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body border-radius-none">
                    <h4>总待审批目的地: {{$statisticData['no_address']['total']}}</h4>
                    <div id="new-designer-users"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    require(['jquery', 'morris'], function($) {
        // 小B用户数统计
        new Morris.Line({
            element: 'new-users',
            resize: true,
            data: {!! $statisticData['addresses']['dayCount'] !!},
            xkey: 'y',
            ykeys: ['value'],
            xLabelFormat: function(date) {
                return (date.getMonth()+1)+'月'+date.getDate()+'日';
            },
            labels: ['人数'],
            lineColors: ['#efefef'],
            hideHover: 'auto',
            gridTextColor: "#fff",
            dateFormat: function(date) {
                d = new Date(date);
                return d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate();
            }
        });
        // 用户数统计
        new Morris.Line({
            element: 'new-designer-users',
            resize: true,
            data: {!! $statisticData['no_address']['dayCount'] !!},
            xkey: 'y',
            ykeys: ['value'],
            xLabelFormat: function(date) {
                return (date.getMonth()+1)+'月'+date.getDate()+'日';
            },
            labels: ['人数'],
            lineColors: ['#efefef'],
            hideHover: 'auto',
            gridTextColor: "#fff",
            dateFormat: function(date) {
                d = new Date(date);
                return d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate();
            }
        });
 });
</script>
@endsection
