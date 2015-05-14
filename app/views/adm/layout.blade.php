<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>VATSIM UK :: Administration Panel</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        {{ HTML::style('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css') }}
        <!-- font Awesome -->
        {{ HTML::style('//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css') }}
        <!-- Ionicons -->
        {{ HTML::style('//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css') }}

        <!-- Morris chart -->
        {{ HTML::style('/assets/css/morris/morris.css') }}
        <!-- jvectormap -->
        {{ HTML::style('/assets/css/jvectormap/jquery-jvectormap-1.2.2.css') }}
        <!-- Date Picker -->
        {{ HTML::style('/assets/css/datepicker/datepicker3.css') }}
        <!-- Daterange picker -->
        {{ HTML::style('/assets/css/daterangepicker/daterangepicker-bs3.css') }}
        <!-- bootstrap wysihtml5 - text editor -->
        {{ HTML::style('/assets/css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}
        <!-- bootstrapSwitch -->
        {{ HTML::style('//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.0.2/css/bootstrap3/bootstrap-switch.min.css') }}
        <!-- bootstrapSelect -->
        {{ HTML::style('//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.5.4/bootstrap-select.min.css') }}
        <!-- Theme style -->
        {{ HTML::style('/assets/css/AdminLTE.css') }}

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
                    VATSIM UK
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
        {{ HTML::script('https://code.jquery.com/jquery-2.1.1.min.js') }}
        <!-- jQuery UI 1.10.3 -->
        {{ HTML::script('https://code.jquery.com/ui/1.11.1/jquery-ui.min.js') }}
        <!-- Bootstrap -->
        {{ HTML::script('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js') }}
        <!-- Morris.js charts -->
        {{ HTML::script('//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js') }}
        {{ HTML::script('/assets/js/plugins/morris/morris.min.js') }}
        <!-- Sparkline -->
        {{ HTML::script('/assets/js/plugins/sparkline/jquery.sparkline.min.js') }}
        <!-- jvectormap -->
        {{ HTML::script('/assets/js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js') }}
        {{ HTML::script('/assets/js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}
        <!-- jQuery Knob Chart -->
        {{ HTML::script('/assets/js/plugins/jqueryKnob/jquery.knob.js') }}
        <!-- daterangepicker -->
        {{ HTML::script('/assets/js/plugins/daterangepicker/daterangepicker.js') }}
        <!-- datepicker -->
        {{ HTML::script('/assets/js/plugins/datepicker/bootstrap-datepicker.js') }}
        <!-- Bootstrap WYSIHTML5 -->
        {{ HTML::script('/assets/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}
        <!-- BootstrapSwitch -->
        {{ HTML::script('//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.0.2/js/bootstrap-switch.min.js') }}
        <!-- BootstrapSelect -->
        {{ HTML::script('//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.5.4/bootstrap-select.min.js') }}
        <!-- BootstrapConfirmation -->
        {{ HTML::script('/assets/js/plugins/bootstrap-confirmation/bootstrap-confirmation.js') }}
        <!-- iCheck -->
        {{ HTML::script('/assets/js/plugins/iCheck/icheck.min.js') }}

        <!-- AdminLTE App -->
        {{ HTML::script('/assets/js/AdminLTE/app.js') }}

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

        @if(App::environment() == "development" OR App::environment() == "staging")
        <script>
              var _vengage = _vengage || [];
              (function(){
              var a, b, c;
              a = function (f) {
              return function () {
              _vengage.push([f].concat(Array.prototype.slice.call(arguments, 0)));
            };
          };
          b = ['load', 'addRule', 'addVariable', 'getURLParam', 'addRuleByParam', 'addVariableByParam', 'trackAction', 'submitFeedback', 'submitResponse', 'close', 'minimize', 'openModal', 'helpers'];
          for (c = 0; c < b.length; c++) {
          _vengage[b[c]] = a(b[c]);
        }
        var t = document.createElement('script'),
        s = document.getElementsByTagName('script')[0];
        t.async = true;
        t.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://s3.amazonaws.com/vetrack/init.min.js';
        s.parentNode.insertBefore(t, s);
        _vengage.push(['pubkey', 'bbcc20a3-02ea-44ec-9c28-e7b055dedff8']);
      })();
      </script>
      @else
      <script>
              var _vengage = _vengage || [];
              (function(){
              var a, b, c;
              a = function (f) {
              return function () {
              _vengage.push([f].concat(Array.prototype.slice.call(arguments, 0)));
            };
          };
          b = ['load', 'addRule', 'addVariable', 'getURLParam', 'addRuleByParam', 'addVariableByParam', 'trackAction', 'submitFeedback', 'submitResponse', 'close', 'minimize', 'openModal', 'helpers'];
          for (c = 0; c < b.length; c++) {
          _vengage[b[c]] = a(b[c]);
        }
        var t = document.createElement('script'),
        s = document.getElementsByTagName('script')[0];
        t.async = true;
        t.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://s3.amazonaws.com/vetrack/init.min.js';
        s.parentNode.insertBefore(t, s);
        _vengage.push(['pubkey', '6e575d09-616e-44a0-a78a-3f38b918d1c6']);
      })();
      </script>
      @endif

        @section('scripts')
        @show
    </body>
</html>
