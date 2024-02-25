<nav id="nav" class="navbar navbar-uk navbar-fixed-top">
    @if (is_local_environment())
    <div class="dev_environment_notification">
        You are in a <b>NON-PRODUCTION</b> environment
    </div>
    @endif
    @include('components.top-notification')
    <div class="nav_upper_container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle nav nav-collapsed" data-toggle="collapse" data-target="#nav-inner">
                <span class="nav-collapsed-icon"></span>
                <span class="nav-collapsed-icon"></span>
                <span class="nav-collapsed-icon"></span>
            </button>
            <a class="navbar-brand" href="{{ route("site.home") }}">
                {!! HTML::image("images/vatsim_uk_logo.png", "UK Logo") !!}
            </a>
        </div>

        <div id="nav-inner" class="collapse navbar-collapse" style="height:100%">
            <ul class="nav navbar-nav navcustom" style="height:100%">
                <li class="nav-item">{!! link_to_route("site.home", "Home") !!}</li>

                <li class="nav-item">{!! link_to_route("site.staff", "Staff") !!}</li>

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

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pilots <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li>{!! link_to_route("site.pilots.landing", "Welcome") !!}</li>
                                <li>{!! link_to_route("site.pilots.ratings", "Ratings") !!}</li>
                                <li>{!! link_to_route("site.pilots.mentor", "Becoming a Mentor") !!}</li>
                                <li>{!! link_to_route("site.pilots.oceanic", "Oceanic Procedures") !!}</li>
                                <li>{!! link_to_route("site.pilots.tfp", "Flying Programme") !!}</li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Flight Training Exercises</li>
                                <li>{!! link_to_route('fte.dashboard', 'Dashboard') !!}</li>
                                <li>{!! link_to_route('fte.guide', 'Guide') !!}</li>
                                <li>{!! link_to_route('fte.exercises', 'Exercises') !!}</li>
                                <li>{!! link_to_route('fte.history', 'Flight History') !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Controllers <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li>{!! link_to_route("site.atc.landing", "Welcome") !!}</li>
                                <li>{!! link_to_route("site.roster.index", "Controller Roster") !!}</li>
                                <li>{!! link_to_route("site.atc.newController", "New Controller (OBS)") !!}</li>
                                <li>{!! link_to_route("site.atc.endorsements", "Endorsements") !!}</li>
                                <li>{!! link_to_route("site.atc.mentor", "Becoming a Mentor") !!}</li>
                                <li>{!! link_to_route("site.atc.bookings", "Bookings") !!}</li>
                                @if(currentUserHasAuth())
                                <li>{!! link_to_route("ukcp.guide", "UK Controller Plugin") !!}</li>
                                @endif
                            </ul>
                        </li>
                        <li class="col-sm-12">
                            <ul>
                                <li class="divider"></li>
                                <li class="dropdown-header">Endorsements</li>
                                <li>{!! link_to_route("controllers.endorsements.gatwick_ground", "Gatwick Ground") !!}</li>
                                <li>{!! link_to_route("site.atc.heathrow", "Heathrow") !!}</li>
                            </ul>
                        </li>
                        <li class="col-sm-12">
                            <ul>
                                <li class="divider"></li>
                                <li class="dropdown-header">Hour Checker</li>
                                <li>{!! link_to_route("controllers.hour_check.area", "C1 Training Place") !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Operations <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li>{!! link_to_route("site.operations.landing", "Welcome") !!}</li>
                                <li>{!! link_to_route("site.airports", "Airfield Information") !!}</li>
                                <li>{!! link_to_route("site.operations.sectors", "Area Sectors") !!}</li>
                                <li>{!! link_to('https://community.vatsim.uk/forum/166-atc-procedure-changes/', "Procedure Changes") !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Community <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Visit / Transfer</li>
                                <li>{!! link_to_route("site.community.vt-guide", "Guide") !!}</li>
                                <li>{!! link_to_route("visiting.landing", "Dashboard") !!}</li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Third-Party Services</li>
                                <li>{!! link_to_route("site.community.teamspeak", "TeamSpeak") !!}</li>
                                <li>{!! link_to_route("discord.show", "Discord") !!}</li>
                                <li class="divider"></li>
                                <li>{!! link_to_route('site.community.terms', "Terms & Conditions") !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Marketing <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li>{!! link_to_route("site.marketing.branding", "Branding Guidelines") !!}</li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">{{ HTML::link('https://community.vatsim.uk/downloads', 'Downloads') }}</li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Other Services <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Network Statistics</li>
                                <li>{!! link_to_route("networkdata.dashboard", "My Statistics") !!}</li>
                                <li>{!! link_to_route("networkdata.online", "Online Users") !!}</li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Waiting Lists</li>
                                <li>{!! link_to_route("mship.waiting-lists.index", "My Waiting Lists") !!}</li>
                                <li class="divider"></li>
                                <li>{{ HTML::link('https://cts.vatsim.uk/', 'CTS') }}</li>
                                <li>{{ HTML::link('https://helpdesk.vatsim.uk/', 'Helpdesk') }}</li>
                                <li>{{ HTML::link('https://community.vatsim.uk/', 'Forum') }}</li>
                                <li>{{ HTML::link('https://events.vatsim.uk/', 'Events') }}</li>
                                <li>{{ HTML::link('https://moodle.vatsim.uk/', 'Moodle') }}</li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>

            @if(currentUserHasAuth())
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
                @if(Auth::user()->can('admin.access'))
                <li class="dropdown dropdown-large">
                    <a href="{{ route("filament.app.pages.dashboard") }}" title="Admin Dashboard">
                        <i class="fa fa-briefcase"></i>
                    </a>
                </li>
                @endif
                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="hidden-md">{{Auth::user()->name.' (' .Auth::user()->id.')'}} <b class="caret"></b></span>
                        <i class="fa fa-sliders visible-md-inline-block"><b class="caret"></b></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-logout dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li>{!! link_to_route("landing", "Dashboard") !!}</li>
                                <li class="divider"></li>
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
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log
                                        Out</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
            @else
            <ul class="nav navbar-nav navbar-right navcustom">
                <li class="dropdown dropdown-large">
                    <a href="{{ route("login") }}" title="Login">
                        Login &thinsp;<i class="fa fa-arrow-right"></i>
                    </a>
                </li>
            </ul>
            @endif
        </div>
    </div>
</nav>
