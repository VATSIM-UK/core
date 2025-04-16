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
            <a class="navbar-brand" href="{{ route('site.home') }}">
                <img src="{{ asset('images/vatsim_uk_logo.png') }}" alt="VATSIM UK Logo" />
            </a>
        </div>

        <div id="nav-inner" class="collapse navbar-collapse" style="height:100%">
            <ul class="nav navbar-nav navcustom" style="height:100%">
                <li class="nav-item"><a href="{{ route('site.home') }}">Home</a></li>

                <li class="nav-item"><a href="{{ route('site.staff') }}">Staff</a></li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Feedback <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li><a href="{{ route('mship.feedback.new') }}">Submit Feedback</a></li>
                                <li><a href="{{ route('mship.feedback.view') }}">View My Feedback</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pilots <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li><a href="{{ route('site.pilots.landing') }}">Welcome</a></li>
                                <li><a href="{{ route('site.pilots.ratings') }}">Ratings</a></li>
                                <li><a href="{{ route('site.pilots.mentor') }}">Become a Mentor</a></li>
                                <li><a href="{{ route('site.pilots.oceanic') }}">Oceanic Procedures</a></li>
                                <li><a href="{{ route('site.pilots.tfp') }}">Flying Programme</a></li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Flight Training Exercises</li>
                                <li><a href="{{ route('fte.dashboard') }}">Dashboard</a></li>
                                <li><a href="{{ route('fte.guide') }}">Guide</a></li>
                                <li><a href="{{ route('fte.exercises') }}">Exercises</a></li>
                                <li><a href="{{ route('fte.history') }}">Flight History</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Controllers <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li><a href="{{ route('site.atc.landing') }}">Welcome</a></li>
                                <li><a href="{{ route('site.roster.index') }}">Controller Roster</a></li>
                                <li><a href="{{ route('site.atc.newController') }}">New Controller (OBS)</a></li>
                                <li><a href="{{ route('site.atc.endorsements') }}">Endorsements</a></li>
                                <li><a href="{{ route('site.atc.mentor') }}">Become a Mentor</a></li>
                                <li><a href="{{ route('site.atc.bookings') }}">Bookings</a></li>
                                @if(currentUserHasAuth())
                                <li><a href="{{ route('ukcp.guide') }}">UK Controller Plugin</a></li>
                                @endif
                            </ul>
                        </li>
                        <li class="col-sm-12">
                            <ul>
                                <li class="divider"></li>
                                <li class="dropdown-header">Endorsements</li>
                                <li><a href="{{ route('controllers.endorsements.gatwick_ground') }}">Gatwick Ground</a></li>
                                <li><a href="{{ route('controllers.endorsements.heathrow_ground_s1') }}">Heathrow Ground (S1)</a></li>
                                <li><a href="{{ route('site.atc.heathrow') }}">Heathrow</a></li>
                            </ul>
                        </li>
                        <li class="col-sm-12">
                            <ul>
                                <li class="divider"></li>
                                <li class="dropdown-header">Hour Checker</li>
                                <li><a href="{{ route('controllers.hour_check.area') }}">C1 Training Place</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Operations <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li><a href="{{ route('site.operations.landing') }}">Welcome</a></li>
                                <li><a href="{{ config('services.docs.url') }}">Documentation</a></li>
                                <li><a href="{{ route('site.airports') }}">Airfield Information</a></li>
                                <li><a href="{{ route('site.operations.sectors') }}">Area Sectors</a></li>
                                <li><a href="https://community.vatsim.uk/forum/166-atc-procedure-changes/">Procedure Changes</a></li>
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
                                <li><a href="{{ route('site.community.vt-guide') }}">Guide</a></li>
                                <li><a href="{{ route('visiting.landing') }}">Dashboard</a></li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Third-Party Services</li>
                                <li><a href="{{ route('site.community.teamspeak') }}">TeamSpeak</a></li>
                                <li><a href="{{ route('discord.show') }}">Discord</a></li>
                                <li class="divider"></li>
                                <li><a href="{{ route('site.community.terms') }}">Terms & Conditions</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Marketing <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li><a href="{{ route('site.marketing.branding') }}">Branding Guidelines</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a href="https://community.vatsim.uk/downloads">Downloads</a></li>

                <li class="dropdown dropdown-large">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Other Services <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-large row mainmenu_dropdown">
                        <li class="col-sm-12">
                            <ul>
                                <li class="dropdown-header">Network Statistics</li>
                                <li><a href="{{ route('networkdata.dashboard') }}">My Statistics</a></li>
                                <li class="divider"></li>
                                <li class="dropdown-header">Waiting Lists</li>
                                <li><a href="{{ route('mship.waiting-lists.index') }}">My Waiting Lists</a></li>
                                <li class="divider"></li>
                                <li><a href="https://cts.vatsim.uk/">CTS</a></li>
                                <li><a href="https://helpdesk.vatsim.uk/">Helpdesk</a></li>
                                <li><a href="https://community.vatsim.uk/">Forum</a></li>
                                <li><a href="https://events.vatsim.uk/">Events</a></li>
                                <li><a href="https://moodle.vatsim.uk/">Moodle</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>

            @if(currentUserHasAuth())
            <ul class="nav navbar-nav navbar-right navcustom">
                <li class="dropdown dropdown-large navbar-notification hidden-xs">
                    <a href="{{ route('mship.notification.list') }}" title="Notifications">
                        @if(Auth::user()->has_unread_notifications)
                        <i class="fa fa-bell unread-notifications"></i>
                        @else
                        <i class="fa fa-bell"></i>
                        @endif
                    </a>
                </li>
                @if(Auth::user()->can('admin.access'))
                <li class="dropdown dropdown-large">
                    <a href="{{ route('filament.app.pages.dashboard') }}" title="Admin Dashboard">
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
                                <li><a href="{{ route('landing') }}">Dashboard</a></li>
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
                                <li class="visible-xs visible-sm"><a href="{{ route('mship.notification.list') }}">Notifications</a></li>
                                <li class="divider"></li>
                                <li><a href="{{ route('password.change') }}">Modify Password</a></li>
                                @if(!Auth::user()->mandatory_password)
                                <li><a href="{{ route('password.delete') }}">Disable Password</a></li>
                                @endif
                                <li class="divider"></li>
                                <li><a href="{{ route('mship.manage.email.add') }}">Add Email Address</a></li>
                                <li><a href="{{ route('mship.manage.email.assignments') }}">Email Assignments</a></li>
                                <li class="divider"></li>
                                <li>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                        @csrf
                                    </form>
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
                    <a href="{{ route('login') }}" title="Login">
                        Login &thinsp;<i class="fa fa-arrow-right"></i>
                    </a>
                </li>
            </ul>
            @endif
        </div>
    </div>
</nav>
