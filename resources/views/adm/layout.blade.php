<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>VATSIM UK :: Administration Panel</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="{{ mix('css/admin-all.css') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @yield('styles')
</head>
<body class="hold-transition skin-black sidebar-mini">

<div class="wrapper">
    <header class="main-header">
        @if (is_local_environment())
            <div class="dev_environment_notification">
                You are in a <b>NON-PRODUCTION</b> environment
            </div>
        @endif
        <a href="{{ URL::route("adm.index") }}" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>V</b>UK</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>VATSIM</b>UK</span>
        </a>

        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
        </nav>

    </header>

    <aside class="main-sidebar" style="{{is_local_environment()?"padding-top:70px":""}}">
        @include('adm.layout.sidebar')
    </aside>

    <div class="content-wrapper">

        <section class="content">

            @include('adm.layout.alerts')

            @yield('content')

        </section>
    </div>

</div>

@include('adm.layout.scripts')

</body>
</html>
