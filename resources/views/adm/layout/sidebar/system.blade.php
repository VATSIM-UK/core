@can('use_permission', "adm/system")
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
@endcan