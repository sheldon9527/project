<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>

@include('admin.common.head')

<body class="fixed skin-red-light sidebar-mini">

    <!-- Header -->
    @include('admin.common.header')

    <!-- Sidebar -->
    @include('admin.common.sidebar')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                @yield('title', '青柚旅行 Admin')
                <small>@yield('page_description', null)</small>
            </h1>
            <!-- You can dynamically generate breadcrumbs here -->
            <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Your Page Content Here -->
            @yield('content')
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    @include('admin.common.footer')
    <!-- ./wrapper -->
</body>

</html>
