<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
        <div class="pull-left image">
            {!! HTML::image("images/default_avatar.png", "User Image", ["class" => "img-circle", "style" => "background: #FFFFFF;"]) !!}
        </div>
        <div class="pull-left info">
            <p>Hello, {{ $_account->name_first }}</p>

            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
    </div>

    <!-- search form -->
    {!! Form::open(["url" => URL::route("adm.search"), "method" => "GET", "class" => "sidebar-form"]) !!}
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search..."/>
        <span class="input-group-btn">
            <button type='submit' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
        </span>
    </div>
    {!! Form::close() !!}
            <!-- /.search form -->

    <ul class="sidebar-menu">
        @if($_account->hasChildPermission("adm/dashboard"))
            <li {!! (\Request::is('adm/dashboard*') ? ' class="active"' : '') !!}>
                <a href="{{ URL::route("adm.dashboard") }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>
            @endif
                    {{-- </li>
        <li>
            <a href="pages/mailbox.html">
                <i class="fa fa-envelope"></i> <span>Mailbox</span>
                <small class="badge pull-right bg-yellow">12</small>
                <small class="badge pull-right bg-red">SOON</small>
            </a>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-table"></i> <span>vData</span>
                <i class="fa fa-angle-left pull-right"></i>
                <small class="badge pull-right bg-red">SOON</small>
            </a>
            <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-angle-double-right"></i> Airline Database</a></li>
                <li><a href="#"><i class="fa fa-angle-double-right"></i> Aircraft Database</a></li>
                <li><a href="{{ URL::to('/adm/navdata/airport') }}"><i class="fa fa-angle-double-right"></i> Airport Database</a></li>
                <li><a href="#"><i class="fa fa-angle-double-right"></i> ATC Database</a></li>
            </ul>
        </li>
        <li>
            <a href="#">
                <i class="ion ion-android-microphone"></i> <span>ATC Bookings</span>
                <small class="badge pull-right bg-blue">1721</small>
                <small class="badge pull-right bg-red">SOON</small>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="ion ion-plane"></i> <span>Pilot Bookings</span>
                <small class="badge pull-right bg-green">3,456</small>
                <small class="badge pull-right bg-red">SOON</small>
            </a>
        </li>
        <li class="treeview">
            <a href="#">
                <i class="fa fa-bar-chart-o"></i>
                <span>Statistics</span>
                <i class="fa fa-angle-left pull-right"></i>
                <small class="badge pull-right bg-red">SOON</small>
            </a>
            <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-angle-double-right"></i> API Calls</a></li>
                <li><a href="#"><i class="fa fa-angle-double-right"></i> Pilot Bookings</a></li>
                <li><a href="#"><i class="fa fa-angle-double-right"></i> ATC Bookings</a></li>
            </ul>
        </li>--> --}}
            @if($_account->hasChildPermission("adm/mship"))
                <li class="treeview {{ (\Request::is('adm/mship*') ? 'active' : '') }}">
                    <a href="#">
                        <i class="ion ion-person-stalker"></i> <span>Membership</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        @if($_account->hasChildPermission("adm/mship/account"))
                            <li {!! (\Request::is('adm/mship/account*') ? ' class="active"' : '') !!}>
                                <a href="{{ URL::route("adm.mship.account.index") }}"><i
                                            class="fa fa-angle-double-right"></i> Accounts List</a>
                            </li>
                        @endif
                        @if($_account->hasChildPermission("adm/mship/staff"))
                            <li {!! (\Request::is('adm/mship/staff*') ? ' class="active"' : '') !!}>
                                <a href="{{ URL::route("adm.mship.staff.index") }}"><i
                                            class="fa fa-angle-double-right"></i> Staff List / Layout</a>
                            </li>
                        @endif

                        @if($_account->hasChildPermission("adm/mship/permission") || $_account->hasChildPermission("adm/mship/role"))
                            <li class="treeview {{ ((\Request::is('adm/mship/permission*') || \Request::is('adm/mship/role*')) ? 'active' : '') }}">
                                <a href="#">
                                    <i class="ion ion-email"></i> <span>Roles &amp; Permissions</span>
                                    <i class="fa fa-angle-left pull-right"></i>
                                </a>
                                <ul class="treeview-menu">
                                    <li {!! (\Request::is('adm/mship/role*') ? ' class="active"' : '') !!}>
                                        <a href="{{ URL::route("adm.mship.role.index") }}"><i
                                                    class="fa fa-angle-double-right"></i> Roles List</a>
                                    </li>
                                    <li {!! (\Request::is('adm/mship/permission*') ? ' class="active"' : '') !!}>
                                        <a href="{{ URL::route("adm.mship.permission.index") }}"><i
                                                    class="fa fa-angle-double-right"></i> Permissions List</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if($_account->hasChildPermission("adm/mship/note"))
                            <li class="treeview {{ (\Request::is('adm/mship/note*') ? 'active' : '') }}">
                                <a href="#">
                                    <i class="ion ion-email"></i> <span>Note Config</span>
                                    <i class="fa fa-angle-left pull-right"></i>
                                </a>
                                <ul class="treeview-menu">
                                    </li>
                                    <li {!! (\Request::is('adm/mship/note') ? ' class="active"' : '') !!}>
                                        <a href="{{ URL::route("adm.mship.note.type.index") }}"><i
                                                    class="fa fa-angle-double-right"></i> Note Type</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            @if($_account->hasPermission("adm/mship/feedback"))
                <li class="treeview {{ ((\Request::is('adm/mship/feedback*')) ? 'active' : '') }}">
                    <a href="#">
                        <i class="ion ion-help"></i> <span>Member Feedback</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        @if($_account->hasPermission("adm/mship/feedback/configure/*"))
                            <li {!! (\Request::is('adm/mship/feedback/new') ? ' class="active"' : '') !!}>
                                <a href="{{ URL::route("adm.mship.feedback.new") }}">
                                    <i class="fa fa-plus"></i>
                                    <span>Add Feedback Form</span>
                                </a>
                            </li>
                        @endif
                        @if($_account->hasPermission("adm/mship/feedback/list"))
                          <li {!! (\Request::is('adm/mship/feedback/list') ? ' class="active"' : '') !!}>
                              <a href="{{ URL::route("adm.mship.feedback.all") }}">
                                <i class="fa fa-bars"></i>
                                <span>All Feedback</span>
                              </a>
                          </li>
                        @endif
                        @foreach($_feedbackForms as $f)
                            @if($_account->hasPermission("adm/mship/feedback/list/".$f->slug))
                                <li {!! (\Request::is('adm/mship/feedback/list/'.$f->slug.'*') ? ' class="active"' : '') !!}>
                                    <a href="{{ URL::route("adm.mship.feedback.form", [$f->slug]) }}">
                                        <i class="fa fa-bars"></i>
                                        <span>{!! $f->name !!}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                        @if($_account->hasPermission("adm/mship/feedback/configure/*"))
                            @foreach($_feedbackForms as $f)
                                <li {!! (\Request::is('adm/mship/feedback/configure/'.$f->id) ? ' class="active"' : '') !!}>
                                    <a href="{{ URL::route("adm.mship.feedback.config", [$f->id]) }}">
                                        <i class="fa fa-cog"></i>
                                        <span>{!! $f->name !!} Settings</span>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </li>
            @endif

            @can('use-permission', 'smartcars')
                <li class="treeview {{ Request::is('adm/smartcars*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="ion ion-paper-airplane"></i> <span>smartCARS</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        @can('use-permission', 'smartcars/aircraft')
                            <li {!! Request::is('adm/smartcars/aircraft*') ? ' class="active"' : '' !!}>
                                <a href="{{ URL::route('adm.smartcars.aircraft.index') }}">
                                    <i class="fa fa-plane"></i>
                                    <span>Aircraft</span>
                                </a>
                            </li>
                        @endcan
                        @can('use-permission', 'smartcars/airports')
                            <li {!! Request::is('adm/smartcars/airports*') ? ' class="active"' : '' !!}>
                                <a href="{{ URL::route('adm.smartcars.airports.index') }}">
                                    <i class="fa fa-road"></i>
                                    <span>Airports</span>
                                </a>
                            </li>
                        @endcan
                        @can('use-permission', 'smartcars/exercises')
                            <li {!! Request::is('adm/smartcars/exercises*') ? ' class="active"' : '' !!}>
                                <a href="{{ URL::route('adm.smartcars.exercises.index') }}">
                                    <i class="fa fa-pencil-square-o"></i>
                                    <span>Exercises</span>
                                </a>
                            </li>
                        @endcan
                        @can('use-permission', 'smartcars/flights')
                            <li {!! Request::is('adm/smartcars/flights*') ? ' class="active"' : '' !!}>
                                <a href="{{ URL::route('adm.smartcars.flights.index') }}">
                                    <i class="fa fa-fighter-jet"></i>
                                    <span>Member Flights</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan

            @if($_account->hasChildPermission("adm/networkdata"))
                @include("network-data.admin._sidebar")
            @endif

            @if($_account->hasChildPermission("adm/visittransfer"))
                @include("visit-transfer.admin._sidebar")
            @endif

            @if($_account->hasChildPermission("adm/system"))
                <li class="treeview {{ (\Request::is('adm/system*') ? 'active' : '') }}">
                    <a href="#">
                        <i class="ion ion-gear-b"></i> <span>System</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li>
                            <a href="{{ URL::route("adm.sys.activity.list") }}" {{ ( Request::is('adm/system/activity*') ? 'active' : '') }}>
                                <i class="fa fa-list"></i> <span>Activity Stream</span>
                            </a>
                        </li>
                        <li class="treeview {{ (\Request::is('adm/system/jobs*') ? 'active' : '') }}">
                            <a href="#">
                                <i class="ion ion-email"></i> <span>Jobs</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li {!! (Request::is('adm/system/jobs/failed*') ? ' class="active"' : '') !!}>
                                    <a href="{{ URL::route('adm.sys.jobs.failed') }}"><i class="fa fa-bars"></i> Failed Jobs</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            @endif
    </ul>
</section>
<!-- /.sidebar -->
