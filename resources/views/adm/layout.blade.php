<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>VATSIM UK :: Administration Panel</title>

        <!--BugSnagScript-->
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

        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        {!! HTML::style('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css') !!}
        <!-- font Awesome -->
        {!! HTML::style('//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css') !!}
        <!-- Ionicons -->
        {!! HTML::style('//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css') !!}

        <!-- Morris chart -->
        {!! HTML::style('/assets/css/morris/morris.css') !!}
        <!-- jvectormap -->
        {!! HTML::style('/assets/css/jvectormap/jquery-jvectormap-1.2.2.css') !!}
                <!-- Date Time Picker -->
        {!! HTML::style('/assets/css/datetimepicker/bootstrap-datetimepicker.min.css') !!}
                <!-- Date Picker -->
        {!! HTML::style('/assets/css/datepicker/datepicker3.css') !!}
                <!-- Time Picker -->
        {!! HTML::style('/assets/css/timepicker/bootstrap-timepicker.min.css') !!}
        <!-- Daterange picker -->
        {!! HTML::style('/assets/css/daterangepicker/daterangepicker-bs3.css') !!}
        <!-- bootstrap wysihtml5 - text editor -->
        {!! HTML::style('/assets/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') !!}
        <!-- bootstrapSwitch -->
        {!! HTML::style('//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.0.2/css/bootstrap3/bootstrap-switch.min.css') !!}
        <!-- bootstrapSelect -->
        {!! HTML::style('//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.5.4/bootstrap-select.min.css') !!}
        <!-- DatetimePicker -->
        {!! HTML::style(asset("assets/css/datetimepickerxdan/jquery.datetimepicker.min.css")) !!}
        <!-- Theme style -->
        {!! HTML::style('/assets/css/AdminLTE.css') !!}

        @section('styles')
        @show

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-black">
        @if(!isset($shellOnly) OR !$shellOnly)
            <header class="header">
                <a href="{{ URL::route("adm.dashboard") }}" class="logo">
                  <!-- Add the class icon to your logo image or logo icon to add the margining -->
                  <div class="hidden-md hidden-lg" style="float:left;">
                    <span data-toggle="offcanvas">
                      <i class="fa fa-bars" aria-hidden="true"></i>
                    </span>
                  </div> VATSIM UK
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <!--<a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>-->
                    <div class="navbar-right">
                        @include('adm.layout.navbar')
                    </div>
                </nav>
            </header>
            <div class="wrapper row-offcanvas row-offcanvas-left">
                <!-- Left side column. contains the logo and sidebar -->
                <aside class="left-side sidebar-offcanvas">
                    @include('adm.layout.sidebar')
                </aside>

                <!-- Right side column. Contains the navbar and content of the page -->
                <aside class="right-side">
                    @include('adm.layout.breadcrumb', array('breadcrumb' => $_breadcrumb, 'title' => $_pageTitle, 'subTitle' => $_pageSubTitle))

                    <!-- Main content -->
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
                    </section><!-- /.content -->
                </aside><!-- /.right-side -->
            </div><!-- ./wrapper -->
        @else
            <section class="content">
                @yield('content')
            </section><!-- /.content -->
        @endif

        <!-- add new calendar event modal -->

        <!-- jQuery 2.0.2 -->
        {!! HTML::script('https://code.jquery.com/jquery-2.1.1.min.js') !!}
        <!-- jQuery UI 1.10.3 -->
        {!! HTML::script('https://code.jquery.com/ui/1.11.1/jquery-ui.min.js') !!}
        <!-- Bootstrap -->
        {!! HTML::script('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js') !!}
        <!-- Morris.js charts -->
        {!! HTML::script('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js') !!}
        {!! HTML::script('/assets/js/plugins/morris/morris.min.js') !!}
        <!-- Sparkline -->
        {!! HTML::script('/assets/js/plugins/sparkline/jquery.sparkline.min.js') !!}
        <!-- jvectormap -->
        {!! HTML::script('/assets/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') !!}
        {!! HTML::script('/assets/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') !!}
        <!-- jQuery Knob Chart -->
        {!! HTML::script('/assets/js/plugins/jqueryKnob/jquery.knob.js') !!}
        <!-- daterangepicker -->
        {!! HTML::script('/assets/js/plugins/daterangepicker/daterangepicker.js') !!}
            <!-- datetimepicker -->
        {!! HTML::script('/assets/js/plugins/datetimepicker/bootstrap-datetimepicker.min.js') !!}
                <!-- datepicker -->
            {!! HTML::script('/assets/js/plugins/datepicker/bootstrap-datepicker.js') !!}
                    <!-- timepicker -->
            {!! HTML::script('/assets/js/plugins/timepicker/bootstrap-timepicker.min.js') !!}
        <!-- Bootstrap WYSIHTML5 -->
        {!! HTML::script('/assets/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') !!}
        <!-- BootstrapSwitch -->
        {!! HTML::script('//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.0.2/js/bootstrap-switch.min.js') !!}
        <!-- BootstrapSelect -->
        {!! HTML::script('//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.5.4/bootstrap-select.min.js') !!}
        <!-- BootstrapConfirmation -->
        {!! HTML::script('/assets/js/plugins/bootstrap-confirmation/bootstrap-confirmation.js') !!}
        <!-- iCheck -->
        {!! HTML::script('/assets/js/plugins/iCheck/icheck.min.js') !!}

        <!-- AdminLTE App -->
        {!! HTML::script('/assets/js/AdminLTE/app.js') !!}

        <script language="javascript" type="text/javascript">
            $('.selectpicker').selectpicker();
        </script>

        <script language="javascript" type="text/javascript">
            $('[data-toggle="confirmation"]').confirmation({
                placement : "top",
                btnOkClass : "btn btn-xs btn-primary",
                btnCancelClass : "btn btn-xs",
                singleton : true,
            });
        </script>

        <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-13128412-6', 'auto');
  ga('send', 'pageview');

</script>

        @section('scripts')
        @show
    </body>
</html>
