<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
        <div class="pull-left image">
            <img src="img/avatar3.png" class="img-circle" alt="User Image" />
        </div>
        <div class="pull-left info">
            <p>Hello, {{ $_user->name_first }}</p>

            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
    </div>

    <!-- search form -->
    {{ Form::open(array("url" => URL::route("adm.search"), "method" => "GET", "class" => "sidebar-form")) }}
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Search..."/>
        <span class="input-group-btn">
            <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
        </span>
    </div>
    {{ Form::close() }}
    <!-- /.search form -->

    <ul class="sidebar-menu">
        <li {{ (\Request::is('adm/dashboard*') ? ' class="active"' : '') }}>
            <a href="{{ URL::route("adm.dashboard") }}">
                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            </a>
        </li>
        <!--</li>
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
        </li>-->
        <li class="treeview {{ (\Request::is('adm/mship*') ? 'active' : '') }}">
            <a href="#">
                <i class="ion ion-person-stalker"></i> <span>Member Accounts</span>
                <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li {{ (\Request::is('adm/mship/account') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.mship.account.index") }}"><i class="fa fa-angle-double-right"></i> Accounts List</a>
                </li>
            </ul>
        </li>
        <li class="treeview {{ (\Request::is('adm/system*') ? 'active' : '') }}">
            <a href="#">
                <i class="ion ion-gear-b"></i> <span>System</span>
                <i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li>
                    <a href="{{ URL::route("adm.sys.timeline") }}">
                        <i class="fa fa-list"></i> <span>Timeline</span>
                    </a>
                </li>
                <li class="treeview {{ (\Request::is('adm/system/postmaster*') ? 'active' : '') }}">
                    <a href="#">
                        <i class="ion ion-email"></i> <span>Postmaster</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li {{ (\Request::is('adm/system/postmaster/queue*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::route("adm.sys.postmaster.queue.index") }}"><i class="fa fa-bars"></i> Postmaster Queue</a>
                        </li>
                        <li {{ (\Request::is('adm/system/postmaster/template*') ? ' class="active"' : '') }}>
                            <a href="{{ URL::route("adm.sys.postmaster.template.index") }}"><i class="ion ion-email"></i> Postmaster Templates</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</section>
<!-- /.sidebar -->