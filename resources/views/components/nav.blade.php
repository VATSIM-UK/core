<div class="nav_upper_container navbar-toggleable-md">
    <div class="logo_container">
        <a href="{{ route("dashboard") }}">
            {!! HTML::image("images/vatsim_uk_logo.png", "UK Logo") !!}
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
                {!! link_to_route("dashboard", "Home") !!}
            </li>
        </ul>

        <ul class="nav navbar-nav navcustom">
            <li class="dropdown dropdown-large">
                {!! link_to_route("site.staff", "Staff") !!}
            </li>
        </ul>

        <ul class="nav navbar-nav navcustom">
            <li class="dropdown dropdown-large">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Feedback <b class="caret"></b></a>
                <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                    <li class="col-sm-12">
                        <ul>
                            <li>{!! link_to_route("mship.feedback.new", "Submit Feedback") !!}</li>
                            <li>{!! link_to_route("mship.feedback.view", "View My Feedback") !!}</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>

        <ul class="nav navbar-nav navcustom">
            <li class="dropdown dropdown-large">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Resources <b class="caret"></b></a>
                <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                    <li class="col-sm-12">
                        <ul>
                            <li>{{ HTML::link('https://vatsim.uk/', 'VATSIM UK Homepage', array("target"=>"_blank")) }}</li>
                            <li>{{ HTML::link('https://cts.vatsim.uk/', 'Central Training System', array("target"=>"_blank")) }}</li>
                            <li>{{ HTML::link('http://www.nats-uk.ead-it.com/public/index.php%3Foption=com_content&task=blogcategory&id=6&Itemid=13.html', 'UK Charts', array("target"=>"_blank")) }}</li>
                            <li>{!! link_to_route("site.airports", "UK Airfields") !!}</li>
                            <li class="divider"></li>
                            <li class="dropdown-header">Network Statistics</li>
                            <li>{!! link_to_route("networkdata.dashboard", "My Statistics") !!}</li>
                            <li>{!! link_to_route("networkdata.online", "Online Users") !!}</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>

        <ul class="nav navbar-nav navcustom">
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
        </ul>

        <ul class="nav navbar-nav navcustom">
            <li class="dropdown dropdown-large">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Controllers <b class="caret"></b></a>
                <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                    @if(Auth::guard('vatsim-sso')->check() && Auth::user()->qualificationAtc->isS1)
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Endorsements</li>
                                <li>{!! link_to_route("controllers.endorsements.gatwick_ground", "Gatwick Ground") !!}</li>
                            </ul>
                        </li>
                    @endif
                    <li class="col-sm-12">
                        <ul>
                            <li>{!! link_to_route("site.atc.landing", "Welcome") !!}</li>
                            <li>{!! link_to_route("site.atc.newController", "New Controller (OBS)") !!}</li>
                            <li>{!! link_to_route("site.atc.progression", "Progression Guide (S1-C3)") !!}</li>
                            <li>{!! link_to_route("site.atc.endorsements", "Endorsements") !!}</li>
                            <li>{!! link_to_route("site.atc.mentor", "Becoming a Mentor") !!}</li>
                            <li>{!! link_to_route("site.atc.bookings", "Bookings") !!}</li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>

        @if(\App\Models\Smartcars\Flight::enabled()->count() > 0)
            <ul class="nav navbar-nav navcustom">
                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pilots <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Flight Training Exercises</li>
                                <li>{!! link_to_route('fte.dashboard', 'Dashboard') !!}</li>
                                <li>{!! link_to_route('fte.guide', 'Guide') !!}</li>
                                {{--<li>{!! link_to_route('fte.map', 'Map') !!}</li>--}}
                                <li>{!! link_to_route('fte.exercises', 'Exercises') !!}</li>
                                <li>{!! link_to_route('fte.history', 'Flight History') !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        @endif

        @if(Auth::guard('vatsim-sso')->check() || Auth::guard('web')->check())
            {!! Form::open(['route' => 'logout', 'id' => 'logout-form']) !!}
            <ul class="nav navbar-nav navcustom navbar-right account-dropdown">
                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle hidden-sm"
                       data-toggle="dropdown">{{Auth::user()->name.' (' .Auth::user()->id.')'}} <b
                                class="caret"></b></a>
                    <a href="#" class="dropdown-toggle visible-sm" data-toggle="dropdown"><i
                                class="fa fa-sliders"></i> <b class="caret"></b></a>
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
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log
                                        Out</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
            {!! Form::close() !!}

            <ul class="nav navbar-nav navcustom navbar-right navbar-notification">
                @if(Auth::user()->hasPermission('adm/dashboard'))
                    <li class="dropdown dropdown-large">
                        <a href="{{ route("adm.dashboard") }}" title="Admin Dashboard">
                            <i class="fa fa-dashboard hidden-xs"></i>
                            <span class="hidden-sm hidden-md hidden-lg">Admin Dashboard</span>
                        </a>
                    </li>
                @endif
                <li class="dropdown dropdown-large hidden-xs">
                    <a href="{{ route("mship.notification.list") }}" title="Notifications">
                        @if(Auth::user()->has_unread_notifications)
                            <i class="fa fa-bell unread-notifications"></i>
                        @else
                            <i class="fa fa-bell"></i>
                        @endif
                    </a>
                </li>
            </ul>
        @endif

    </div>
</div>