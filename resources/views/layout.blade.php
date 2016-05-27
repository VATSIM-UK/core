<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <title>VATSIM UK | United Kingdom Division of VATSIM.net</title>

    <!-- CSS -->
    {!! HTML::style('//fonts.googleapis.com/css?family=Yellowtail') !!}
    {!! HTML::style('//fonts.googleapis.com/css?family=Josefin+Slab:600') !!}
    {!! HTML::style(elixir("css/app-all.css")) !!}
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
            <div class="collapse navbar-collapse js-navbar-collapse">
                <ul class="nav navbar-nav navcustom">
                    <li class="dropdown dropdown-large">
                        {!! link_to_route("default", "Home") !!}
                    </li>
                </ul>
                <ul class="nav navbar-nav navcustom">
                    <li class="dropdown dropdown-large">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Account <b class="caret"></b></a>
                        <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                            <li class="col-sm-6">
                                <ul>
                                    <li class="dropdown-header">Password</li>
                                    <li>{!! link_to_route("mship.security.replace", "Modify") !!}</li>
                                    <li>{!! link_to_route("mship.security.replace", "Disable", [1]) !!}</li>
                                    <li class="divider"></li>
                                    <li class="dropdown-header">Email Address</li>
                                    <li>{!! link_to_route("mship.manage.email.add", "Add Email") !!}</li>
                                    <li>{!! link_to_route("mship.manage.email.assignments", "SSO Assignments") !!}</li>
                                </ul>
                            </li>
                            <li class="col-sm-6">
                                <ul>
                                    <li class="dropdown-header">Third-Party</li>
                                    <li>{!! link_to_route("teamspeak.new", "TS Registration") !!}</li>
                                    <li>{!! link_to_route("slack.new", "Slack Registration") !!}</li>
                                </ul>
                            </li>
                        </ul>

                    </li>
                </ul>
                <!--<ul class="nav navbar-nav navcustom">
                    <li class="dropdown dropdown-large">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">RTS <b class="caret"></b></a>
                        <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                            <li class="col-sm-4">
                                <ul>
                                    <li class="dropdown-header">Sessions</li>
                                    <li><a href="#">Management</a></li>
                                    <li><a href="#">History</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown-header">Exams</li>
                                    <li><a href="#">Theory Exams</a></li>
                                    <li><a href="#">Theory Results</a></li>
                                    <li><a href="#">Practical Exam</a></li>
                                    <li><a href="#">Practical Exam History</a></li>
                                </ul>
                            </li>
                            <li class="col-sm-4">
                                <ul>
                                    <li class="dropdown-header">Self</li>
                                    <li><a href="#">Signature</a></li>
                                    <li><a href="#">Email Settings</a></li>
                                    <li><a href="#">Display Settings</a></li>
                                    <li><a href="#">Default Booking Times</a></li>
                                    <li><a href="#">My Details</a></li>
                                    <li><a href="#">Email Member</a></li>
                                </ul>
                            </li>
                            <li class="col-sm-4">
                                <ul>
                                    <li class="dropdown-header">RTS</li>
                                    <li><a href="#">Transfer</a></li>
                                    <li><a href="#">Visit</a></li>
                                    <li class="divider"></li>
                                    <li class="dropdown-header">System</li>
                                    <li><a href="#">Bookings Calendar</a></li>
                                    <li><a href="#">Solo Endorsements</a></li>
                                    <li><a href="#">Special Endorsements</a></li>
                                    <li><a href="#">Visiting Controllers</a></li>
                                    <li><a href="#">System Updates</a></li>
                                </ul>
                            </li>
                        </ul>

                    </li>
                </ul>-->
                <ul class="nav navbar-nav navcustom navbar-right">
                    <li><a href="#">LOG OUT</a></li>
                </ul>

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
        <div class="row">
            @if(Session::has('error') OR isset($error))
                <div class="alert alert-danger" role="alert">
                    <strong>Error!</strong> {!! Session::has('error') ? Session::pull("error") : $error !!}
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

        <div class="row">
            @yield('content', "No content to display")
        </div>

    </div>

</div>

    {!! HTML::script(elixir("js/app-all.js")) !!}

    @yield('scripts')

</body>

</html>
