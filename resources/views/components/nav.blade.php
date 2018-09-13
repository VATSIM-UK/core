<nav class="navbar navbar-uk navbar-fixed-top">
    @if (is_local_environment())
        <div class="dev_environment_notification">
            You are in a <b>NON-PRODUCTION</b> environment
        </div>
    @endif
    <div class="nav_upper_container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle nav nav-collapsed" data-toggle="collapse" data-target="#nav">
                <span class="nav-collapsed-icon"></span>
                <span class="nav-collapsed-icon"></span>
                <span class="nav-collapsed-icon"></span>
            </button>
            <a class="navbar-brand" href="{{ route("dashboard") }}">
                {!! HTML::image("images/vatsim_uk_logo.png", "UK Logo") !!}
            </a>
        </div>

        <div class="collapse navbar-collapse" id="nav" style="height:100%">
            <ul class="nav navbar-nav navcustom"  style="height:100%">
                <li class="dropdown dropdown-large"  style="height:100%">
                    {!! link_to_route("dashboard", "Home") !!}
                </li>
                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Feedback <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li>{!! link_to_route("mship.feedback.new", "Submit Feedback") !!}</li>
                                <li>{!! link_to_route("mship.feedback.view", "View My Feedback") !!}</li>
                            </ul>
                        </li>
                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Community <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Membership</li>
                                <li>{!! link_to_route("visiting.landing", "Visit/Transfer") !!}</li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Third-Party Services</li>
                                <li>{!! link_to_route("teamspeak.new", "TS Registration") !!}</li>
                                <li>{!! link_to_route("slack.new", "Slack Registration") !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>
                @if(Auth::guard('vatsim-sso')->check() && Auth::user()->qualificationAtc && Auth::user()->qualificationAtc->isS1)
                    <li class="dropdown dropdown-large">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Controllers <b class="caret"></b></a>
                        <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                            <li class="col-sm-12">
                                <ul>
                                    <li class="dropdown-header">Endorsements</li>
                                    <li>{!! link_to_route("controllers.endorsements.gatwick_ground", "Gatwick Ground") !!}</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endif
                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pilots <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Flight Training Exercises</li>
                                <li>{!! link_to_route('fte.dashboard', 'Dashboard') !!}</li>
                                <li>{!! link_to_route('fte.guide', 'Guide') !!}</li>
                                <li>{!! link_to_route('fte.exercises', 'Exercises') !!}</li>
                                <li>{!! link_to_route('fte.history', 'Flight History') !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
            @if(Auth::guard('vatsim-sso')->check() || Auth::guard('web')->check())
                <ul class="nav navbar-nav navbar-right navcustom">
                    <li class="dropdown dropdown-large navbar-notification hidden-xs">
                        <a href="{{ route("mship.notification.list") }}" title="Notifications">
                            @if(Auth::user()->has_unread_notifications)
                                <i class="fa fa-bell unread-notifications"></i>
                            @else
                                <i class="fa fa-bell"></i>
                            @endif
                        </a>
                    </li>
                    @if(Auth::user()->hasPermission('adm/dashboard'))
                        <li class="dropdown dropdown-large">
                            <a href="{{ route("adm.dashboard") }}" title="Admin Dashboard">
                                <i class="fa fa-dashboard"></i>
                            </a>
                        </li>
                    @endif
                    <li class="dropdown dropdown-large">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <span class="hidden-sm">{{Auth::user()->name.' (' .Auth::user()->id.')'}} <b
                                            class="caret"></b></span>
                            <i class="fa fa-sliders visible-sm-inline-block"><b class="caret"></b></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-logout dropdown-menu-large row mainmenu_dropdown">
                            <li class="col-sm-12">
                                <ul>
                                    <li><a>ATC Rating: <b>
                                                @if(Auth::user()->qualification_atc == "")
                                                    OBS
                                                @else
                                                    {{ Auth::user()->qualification_atc }}
                                                @endif
                                            </b></a></li>
                                    <li><a>Pilot Rating(s): <b>
                                                {{ (Auth::user()->toArray())['pilot_rating'] }}
                                                @if((Auth::user()->toArray())['pilot_rating'] == "")
                                                    P0
                                                @endif
                                            </b></a></li>
                                    <li class="visible-xs visible-sm">{{ link_to_route("mship.notification.list","Notifications") }}</li>
                                    <li class="divider"></li>
                                    <li>{!! link_to_route('password.change', "Modify Password") !!}</li>
                                    @if(!Auth::user()->mandatory_password)
                                        <li>{!! link_to_route("password.delete", "Disable Password") !!}</li>
                                    @endif
                                    <li class="divider"></li>
                                    <li>{!! link_to_route("mship.manage.email.add", "Add Email Address") !!}</li>
                                    <li>{!! link_to_route("mship.manage.email.assignments", "Email Assignments") !!}</li>
                                    <li class="divider"></li>
                                    <li>
                                        {!! Form::open(['route' => 'logout', 'id' => 'logout-form']) !!}
                                        {!! Form::close() !!}
                                        <a href="{{ route('logout') }}"
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log
                                            Out</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</nav>