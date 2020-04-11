<head>
    <meta charset="UTF-8">
    <title>@yield('title', '青柚旅行 Admin')</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap 3.3.2 -->
    <link href="/bower/AdminLTE/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="/bower/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="/bower/Ionicons/css/ionicons.min.css">
    <!-- style.min.css -->
    <link rel="stylesheet" href="/css/style.min.css">
    <!-- Theme style -->
    <link href="/bower/AdminLTE/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />

    <!-- morris.css -->
    <link rel="stylesheet" href="/bower/AdminLTE/plugins/morris/morris.css">

    <link rel="stylesheet" href="/bower/bootstrap-select/dist/css/bootstrap-select.min.css">

    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect.
    -->
    <link href="/bower/AdminLTE/dist/css/skins/skin-red-light.min.css" rel="stylesheet" type="text/css" />

    <!-- Datepicker -->
    <link href="/bower/AdminLTE/plugins/datepicker/datepicker3.css" rel="stylesheet" />

    <!-- Datetimepicker -->
    <link rel="stylesheet" href="/bower/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />

    <!-- Dropzone ***-->
    <link href='/bower/dropzone/dist/min/dropzone.min.css' rel="stylesheet" />
    <link href='/bower/dropzone/dist/min/basic.min.css' rel="stylesheet" />

    {{-- rich text --}}
    <link href="/bower/google-code-prettify/src/prettify.css" rel="stylesheet" />
    <link href='/bower/bootstrap-wysiwyg/css/style.css' rel="stylesheet" />

    <link href='/bower/bootstrap-tagsinput/dist/bootstrap-tagsinput.css' rel="stylesheet" />
    <link href='/bower/bootstrap-tagsinput/dist/bootstrap-tagsinput-typeahead.css' rel="stylesheet" />

    <link href='/css/admin.css' rel="stylesheet" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/bower/html5shiv/dist/html5shiv.min.js"></script>
    <script src="/bower/respond/dest/respond.min.js"></script>
    <![endif]-->

    {{-- requrejs --}}

    <script src="/bower/requirejs/require.js"></script>
    <script src="/js/admin.js"></script>
	<!-- <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script> -->
	<!-- <script src="http://cdn.bootcss.com/bootstrap/3.3.4/js/bootstrap.min.js"></script> -->
<!-- <script src="http://api.map.baidu.com/api?v=2.0&ak=2d12993ce41407db4050140fe342d9ba" type="text/javascript"></script> -->
</head>
