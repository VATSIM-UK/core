<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>VATSIM UK :: Administration Panel</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ mix('css/admin-all.css') }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    @section('styles')
    @show

    {{-- BugSnag --}}
    <script src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-3.min.js"
            data-apikey="b3be4a53f2e319e1fa77bb3c85a3449d"
            data-releasestage="{{ env('APP_ENV') }}">
        Bugsnag.notifyReleaseStages = ["staging", "production"];

        @if(Auth::check())
            Bugsnag.user = {
            id: {{ Auth::user()->id }},
            name: "{{ Auth::user()->name }}",
            email: "{{ Auth::user()->email }}"
        };
        @endif
    </script>
</head>
<body class="hold-transition skin-black sidebar-mini">
<div class="wrapper">

    <header class="main-header">

        <a href="{{ URL::route("adm.dashboard") }}" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>V</b>UK</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>VATSIM</b>UK</span>
        </a>

        <nav class="navbar navbar-static-top">
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            @include('adm.layout.logout')
        </nav>

    </header>

    <aside class="main-sidebar">
        @include('adm.layout.sidebar')
    </aside>

    <div class="content-wrapper">
    @include('adm.layout.breadcrumb', array('breadcrumb' => $_breadcrumb, 'title' => $_pageTitle, 'subTitle' => $_pageSubTitle))

        <section class="content">

            @if(Session::has('error') OR isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> {{ Session::has('error') ? Session::pull("error") : $error }}
                </div>
            @endif

            @if(count($errors) > 0)
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> There were some errors with your request:
                    <ul>
                        @foreach($errors->getMessages() as $e)
                            <li>{{ $e[0] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(Session::has('success') OR isset($success))
                <div class="alert alert-success" role="alert">
                    <strong>Success!</strong> {{ Session::has('success') ? Session::pull("success") : $success }}
                </div>
            @endif

            @yield('content')
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha384-xBuQ/xzmlsLoJpyjoggmTEz8OWUFM0/RC5BsqQBDX2v5cMvDHcMakNTNrHIW2I5f" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<!-- Morris.js charts -->
<script src='//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js'></script>
<script src='/AdminLTE/js/plugins/morris/morris.min.js'></script>
<!-- Sparkline -->
<script src='/AdminLTE/js/plugins/sparkline/jquery.sparkline.min.js'></script>
<!-- jvectormap -->
<script src='/AdminLTE/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js'></script>
<script src='/AdminLTE/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js'></script>
<!-- jQuery Knob Chart -->
<script src='/AdminLTE/js/plugins/knob/jquery.knob.js'></script>
<!-- daterangepicker -->
<script src='/AdminLTE/js/plugins/daterangepicker/moment.js'></script>
<script src='/AdminLTE/js/plugins/daterangepicker/daterangepicker.js'></script>
<!-- datepicker -->
<script src='/AdminLTE/js/plugins/datepicker/bootstrap-datepicker.js'></script>
<!-- timepicker -->
<script src='/AdminLTE/js/plugins/timepicker/bootstrap-timepicker.min.js'></script>
<!-- Bootstrap WYSIHTML5 -->
<script src='/AdminLTE/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js'></script>
<!-- BootstrapSwitch -->
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.0.2/js/bootstrap-switch.min.js'></script>
<!-- BootstrapSelect -->
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.5.4/bootstrap-select.min.js'></script>
<!-- BootstrapConfirmation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-confirmation/1.0.5/bootstrap-confirmation.min.js" integrity="sha384-vMMciU9KnFBubM1yw+Q+6f68+ZHeeD0LPvydPm6xdw75vMiYRB03L7+4K5gGoh5w" crossorigin="anonymous"></script><!-- iCheck -->
<script src='/AdminLTE/js/plugins/iCheck/icheck.min.js'></script>

<!-- AdminLTE App -->
<script src='/AdminLTE/js/app.min.js'></script>

<script language="javascript" type="text/javascript">
    $('.selectpicker').selectpicker();
    $('[data-toggle="confirmation"]').confirmation({
        placement: "top",
        btnOkClass: "btn btn-xs btn-primary",
        btnCancelClass: "btn btn-xs",
        singleton: true,
        onConfirm: function (event, element) {onConfirm(event, element)},
    });

    // override this function to implement
    function onConfirm(event, element) {}

</script>

<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-13128412-6', 'auto');
    ga('send', 'pageview');

</script>

@section('scripts')
@show
</body>
</html>
