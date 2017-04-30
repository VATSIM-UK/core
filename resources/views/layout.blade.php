<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <title>VATSIM UK | United Kingdom Division of VATSIM.net</title>

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

    <!-- CSS -->
    {!! HTML::style('//fonts.googleapis.com/css?family=Yellowtail') !!}
    {!! HTML::style('//fonts.googleapis.com/css?family=Josefin+Slab:600') !!}
    {!! HTML::style(elixir("css/app-all.css")) !!}
    @yield('styles')
</head>
<body>
<div class="container-fluid">

    <div class="header_container">

        <div class="nav_upper_container navbar-fixed-top">
            <div class="logo_container">
                <a href="{{ route("default") }}">
                    {!! HTML::image("assets/images/vatsim_uk_logo.png", "UK Logo", ["align" => "left", "height" => "70%"]) !!}
                </a>
            </div>

            <button type="button" class="navbar-toggle nav nav-collapsed" data-toggle="collapse" data-target="#nav">
                <span class="nav-collapsed-icon"></span>
                <span class="nav-collapsed-icon"></span>
                <span class="nav-collapsed-icon"></span>
            </button>

            <div class="collapse navbar-collapse js-navbar-collapse" id="nav">
                <ul class="nav navbar-nav navcustom">
                    <li class="dropdown dropdown-large">
                        {!! link_to_route("default", "Home") !!}
                    </li>
                </ul>

                <ul class="nav navbar-nav navcustom">
                    <li class="dropdown dropdown-large">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Community <b class="caret"></b></a>
                        <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                            <li class="col-sm-6">
                                <ul>
                                    @if(Module::isEnabled("visittransfer"))
                                        <li class="dropdown-header">Division Membership</li>
                                        <li>{!! link_to_route("visiting.landing", "Visiting the UK") !!}</li>
                                        <li>{!! link_to_route("visiting.landing", "Transfer to the UK") !!}</li>
                                        <li>{!! link_to_route("visiting.landing", "Leaving the UK") !!}</li>
                                    @endif

                                    @if(Module::isEnabled("visittransfer") && true)
                                        <li class="divider"></li>
                                    @endif

                                    @if(Module::isEnabled("networkdata"))
                                        <li class="dropdown-header">Network Data</li>
                                        <li>{!! link_to_route("networkdata.online", "Who's online") !!}</li>
                                    @endif
                                </ul>
                            </li>
                            <li class="col-sm-6">
                                <ul>
                                    <li class="dropdown-header">Third-Party Services</li>
                                    <li>{!! link_to_route("teamspeak.new", "TS Registration") !!}</li>
                                    <li>{!! link_to_route("slack.new", "Slack Registration") !!}</li>
                                    <li class="divider"></li>
                                    <li class="dropdown-header">Community Features</li>
                                    <li>{!! link_to_route("mship.notification.list", "View Notifications") !!}</li>
                                    <li>{!! link_to_route("mship.email", "Email a Member") !!}</li>
                                    <li>{!! link_to_route("mship.feedback.new", "Controller/Pilot Feedback") !!}</li>
                                </ul>
                            </li>
                        </ul>

                    </li>
                </ul>

                @if(Auth::check())
                    <ul class="nav navbar-nav navcustom">
                        <li class="dropdown dropdown-large">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>
                            <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                                <li class="col-sm-6">
                                    <ul>
                                        <li class="dropdown-header">Password</li>
                                        <li>{!! link_to_route("mship.security.replace", "Modify") !!}</li>
                                        @if(!Auth::user()->mandatory_password)
                                            <li>{!! link_to_route("mship.security.replace", "Disable", [1]) !!}</li>
                                        @endif
                                        <li class="divider"></li>
                                        <li class="dropdown-header">Email Address</li>
                                        <li>{!! link_to_route("mship.manage.email.add", "Add Email") !!}</li>
                                        <li>{!! link_to_route("mship.manage.email.assignments", "SSO Assignments") !!}</li>
                                    </ul>
                                </li>
                                <li class="col-sm-6">
                                    <ul>
                                        <li class="dropdown-header">Third-Party Services</li>
                                        <li>{!! link_to_route("teamspeak.new", "TS Registration") !!}</li>
                                        <li>{!! link_to_route("slack.new", "Slack Registration") !!}</li>
                                    </ul>
                                </li>
                            </ul>

                        </li>
                    </ul>
                @endif
                @if(Auth::check())
                    {!! Form::open(['route' => 'logout', 'id' => 'logout-form']) !!}
                    <ul class="nav navbar-nav navcustom navbar-right">
                        <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">LOG OUT</a></li>
                    </ul>
                    {!! Form::close() !!}
                @endif

            </div>
        </div>

        <div class="banner hidden-xs hidden-sm">
        </div>

        <div class="breadcrumb_container hidden-xs hidden-sm">
            <div class="breadcrumb_content">
                <a href="#">VATSIM UK</a>  /  Home
            </div>
        </div>

        <div class="banner_breadcrumb_spacer visible-xs visible-sm">
        </div>
    </div>

    <div class="page_content">
        <div class="container-fluid">
            @if(Session::has('error') OR isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> {!! Session::has('error') ? Session::pull("error") : $error !!}
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> There is something wrong with your request:
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(Session::has('success') OR isset($success))
                <div class="alert alert-success" role="alert">
                    <strong>Success!</strong> {!! Session::has('success') ? Session::pull("success") : $success !!}
                </div>
            @endif

            @if(Auth::check() && Auth::user()->auth_extra && !Request::is("mship/notification*") && Auth::user()->has_unread_notifications)
                <div class="alert alert-warning" role="alert">
                    You currently have unread notifications. You can view them on the "{!! HTML::link(route("mship.notification.list"), "notifications page") !!}".
                </div>
            @endif
        </div>

        <div class="container-fluid">
            @yield('content', "No content to display")
        </div>

    </div>

</div>

    {!! HTML::script(elixir("js/app-all.js")) !!}
    
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-13128412-6', 'auto');
      ga('send', 'pageview');

    </script>

    <script type="text/javascript">
        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/57bb3bfca767d83b45e79605/1aqq3gev7';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();

        @if(Auth::check())
                Tawk_API.visitor = {
            name: "{{ Auth::user()->name }} ({{ Auth::user()->id }})",
            email: "{{ Auth::user()->email }}"
        };
        @endif

        Tawk_API.onLoad = function(){
            Tawk_API.addEvent('visited-page', {
                'FullURL'    : '{{ Request::fullUrl() }}',
            }, function(error){});
        };
    </script>

    @yield('scripts')

</body>

</html>
