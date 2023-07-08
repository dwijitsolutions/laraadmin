<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        @hasSection('htmlheader_title')
            @yield('htmlheader_title') -
        @endif{{ LAConfig::getByKey('sitename') }}
    </title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <link rel="shortcut icon" type="image/ico" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap 3.3.4 -->
    <link href="{{ asset('la-assets/css/bootstrap.css') }}" rel="stylesheet" type="text/css" />

    <!-- Libraries -->
    <link href="{{ asset('la-assets/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('la-assets/css/ionicons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('la-assets/plugins/datatables/css/datatables.bootstrap.css') }}" />
    <link href="{{ asset('la-assets/plugins/colorpicker/bootstrap-colorpicker.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('la-assets/plugins/bootstrap-editable/bootstrap-editable.css') }}" rel="stylesheet" />
    <link href="{{ asset('la-assets/plugins/bootstrap-timepicker/bootstrap-timepicker.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('la-assets/plugins/pace/pace.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('la-assets/plugins/iCheck/square/blue.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('la-assets/plugins/summernote/summernote.css') }}" rel="stylesheet" type="text/css" />

    <!-- Theme style -->
    <link href="{{ asset('la-assets/css/LaraAdmin.css') }}?{{ filemtime(base_path('public/la-assets/css/LaraAdmin.css')) }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('la-assets/css/skins/' . LAConfig::getByKey('skin') . '.css') }}" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @stack('styles')

</head>
