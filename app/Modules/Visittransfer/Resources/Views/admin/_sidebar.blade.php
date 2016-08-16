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

        <li {!! (\Request::is('adm/visit-transfer/facility') ? 'class="active"' : '') !!}>
            <a href="{{ URL::route("visiting.admin.facility") }}">
                <i class="ion ion-ios-gear-outline"></i> <span> Facility Settings</span>
            </a>
        </li>

        <li class="treeview {{ (\Request::is('adm/visit-transfer/application*') ? 'active' : '') }}">
            <a href="#">
                <i class="ion ion-briefcase"></i>
                <span>Applications</span>

                <i class="fa fa-angle-left pull-right"></i>
            </a>

            <ul class="treeview-menu">

                <li {{ (\Request::is('adm/visit-transfer/application/lists') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("visiting.admin.application.list") }}">
                        <i class="fa fa-bars"></i>
                        <span>All Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-blue">{!! $visittransfer_statistics_applications_total !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists/open') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("visiting.admin.application.list", ["open"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Open Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{!! $visittransfer_statistics_applications_open !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists/closed') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("visiting.admin.application.list", ["closed"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Closed Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-red">{!! $visittransfer_statistics_applications_closed !!}</small>
                        </span>
                    </a>
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
                    <a href="{{ URL::route("visiting.admin.reference") }}">
                        <i class="fa fa-bars"></i>
                        <span>All References</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-blue">{!! $visittransfer_statistics_references_total !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/approval') ? ' class="active"' : '') }}>
                    <a href="#">
                        <i class="fa fa-bars"></i>
                        <span>Pending Approval</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-red">{!! $visittransfer_statistics_references_pending_approval !!}</small>
                        </span>

                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/pending') ? ' class="active"' : '') }}>
                    <a href="#">
                        <i class="fa fa-bars"></i>
                        <span>Pending Submission</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-orange">{!! $visittransfer_statistics_references_pending_submission !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/submitted') ? ' class="active"' : '') }}>
                    <a href="#">
                        <i class="fa fa-bars"></i>
                        <span>Submitted References</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{!! $visittransfer_statistics_references_submitted!!}</small>
                        </span>
                    </a>
                </li>

            </ul>
        </li>

    </ul>
</li>