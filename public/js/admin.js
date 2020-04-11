require.config({
    baseUrl: "/js/",
    paths: {
        jquery: "../bower/jquery/dist/jquery.min",
        bootstrap: '../bower/bootstrap/dist/js/bootstrap.min',
        bselect: '../bower/bootstrap-select/dist/js/bootstrap-select.min',
        bootbox: '../bower/bootbox.js/bootbox',
        adminLTE: '../bower/AdminLTE/dist/js/app.min',
        slimscroll: '../bower/AdminLTE/plugins/slimScroll/jquery.slimscroll.min',
        morris: '../bower/AdminLTE/plugins/morris/morris.min',
        raphael: '../bower/raphael/raphael-min',
        datepicker: '../bower/AdminLTE/plugins/datepicker/bootstrap-datepicker',
        timepicker: '../bower/AdminLTE/plugins/timepicker/bootstrap-timepicker.min',
        datetimepicker: '../bower/eonasdan-bootstrap-datetimepicker/src/js/bootstrap-datetimepicker',
        moment: '../bower/moment/min/moment.min',
        dropzone: '../bower/dropzone/dist/min/dropzone-amd-module.min',
        cropit: '../bower/cropit/dist/jquery.cropit',
        wysiwyg: '../bower/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min',
        hotkeys: '../bower/jquery.hotkeys/jquery.hotkeys',
        distpicker: '../bower/distpicker/dist/distpicker.min',
        countrypickerData: '/js/distpicker.data.min',
        tagsinput: '../bower/bootstrap-tagsinput/dist/bootstrap-tagsinput.min',
        typeahead: '../bower/typeahead.js/dist/typeahead.jquery',
        bloodhound: '../bower/typeahead.js/dist/bloodhound.min',
        jqueryUi: '../bower/jquery-ui/jquery-ui.min'
    },
    shim: {
        adminLTE: ['bootstrap', 'slimscroll'],
        morris: ['jquery', 'raphael'],
        bootbox: {
            deps: ['jquery', 'bootstrap']
        },
        bootstrap: {
            deps: ['jquery'],
            exports: 'jQuery'
        },
        bselect: {
            deps: ['bootstrap']
        },
        datepicker: ['jquery'],
        dropzone: ['jquery'],
        wysiwyg: {
            deps: ['jquery', 'bootstrap']
        },
        slimscroll: ['jquery'],
        bloodhound: {
            exports: 'Bloodhound'
        },
        tagsinput: ['jquery', 'typeahead'],
        typeahead: {
            deps: ['jquery'],
            init: function($) {
                return require.s.contexts._.registry['typeahead.js'].factory($);
            }
        },
        bloodhound: {
            deps: ['jquery'],
            exports: 'Bloodhound'
        }
    }
});

require(['jquery', 'datepicker', 'bselect'], function($, datepicker, selectpicker) {
    // dropdown  start
    $('.selectpicker').selectpicker({
        style: 'btn-default',
    });
    $('.btn-search').click(function() {
        var $search = $(this).next('.dropdown-search');
        $search.toggle();
        return false;
    });
    // dropdown end

    // input 时间选择
    $('input.datetime').datepicker({
        title: false,
        todayBtn: 'linked',
        todayHighlight: 'true',
        autoclose: true,
        format: "yyyy-mm-dd"
    });

     //dropzone 编辑图片时, 删除旧的附件图片
    $('a.dz-remove').click(function() {
        $(this).closest('.dz-preview').remove();
    });

    // 改变用户状态，激活、未激活
    $('a.status').click(function() {
        var userId = $(this).data().id;
        $.ajax({
            url: '/manager/users/' + userId + '/status',
            type: 'put',
            dataType: 'json',
            success: function(response) {
                window.location.reload();
            }
        });
    });

    // input 图片预览
    $(document).on('change', '.btn-file :file', function() {

        var preview = $(this).data('image-preview');

        if (preview) {
            if (this.files && this.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $(preview).attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
        }
    });
    // 分类
    $('#myTabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

require(['laravel', 'adminLTE']);
