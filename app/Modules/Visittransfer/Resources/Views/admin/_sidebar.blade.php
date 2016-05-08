<li class="treeview {{ (\Request::is('adm/visit-transfer*') ? 'active' : '') }}">

    <a href="#">
        <i class="ion ion-gear-b"></i> <span>Visiting &amp; Transferring</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>

    <ul class="treeview-menu">

        <li {!! (\Request::is('adm/visit-transfer') ? 'class="active"' : '') !!}>
            <a href="{{ URL::route("visiting.admin.dashboard") }}">
                <i class="ion ion-ios-gear-outline"></i> <span> Dashboard</span>
            </a>
        </li>

        <li>
            <a href="{{ URL::route("adm.sys.activity.list") }}" {{ (\Request::is('adm/visit-transfer/facility*') ? 'active' : '') }}>
                <i class="ion ion-ios-gear-outline"></i> <span>Facility Settings</span>
            </a>
        </li>

        <li class="treeview {{ (\Request::is('adm/visit-transfer/application*') ? 'active' : '') }}">
            <a href="#">
                <i class="ion ion-briefcase"></i> <span>Applications</span>
                <i class="fa fa-angle-left pull-right"></i>
            </a>

            <ul class="treeview-menu">

                <li {{ (\Request::is('adm/visit-transfer/application/lists') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> All Applications</a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists?state=open') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> Open Applications</a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists?state=closed') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> Closed Applications</a>
                </li>

            </ul>

        </li>

        <li class="treeview {{ (\Request::is('adm/visit-transfer/reference*') ? 'active' : '') }}">
            <a href="#">
                <i class="ion ion-briefcase"></i> <span>References</span>
                <i class="fa fa-angle-left pull-right"></i>
            </a>

            <ul class="treeview-menu">
                
                <li {{ (\Request::is('adm/visit-transfer/reference/all') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> All References</a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/approval') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> Pending Approval</a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/pending') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> Pending Submission</a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/submitted') ? ' class="active"' : '') }}>
                    <a href="#"><i class="fa fa-bars"></i> Submitted References</a>
                </li>

            </ul>
        </li>

    </ul>
</li>