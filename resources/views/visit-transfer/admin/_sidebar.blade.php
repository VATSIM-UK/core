<li class="treeview {{ (\Request::is('adm/visit-transfer*') ? 'active' : '') }}">

    <a href="#">
        <i class="ion ion-gear-b"></i> <span>Visiting &amp; Transferring</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>

    <ul class="treeview-menu">

        <li {!! (\Request::is('adm/visit-transfer/facility') ? 'class="active"' : '') !!}>
            <a href="{{ URL::route("adm.visiting.facility") }}">
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
                    <a href="{{ URL::route("adm.visiting.application.list") }}">
                        <i class="fa fa-bars"></i>
                        <span>All Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-blue">{!! \App\Models\VisitTransferLegacy\Application::statisticTotal() !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists/open') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.application.list", ["open"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Open Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{!! \App\Models\VisitTransferLegacy\Application::statisticOpenNotInProgress() !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists/review') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.application.list", ["review"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Review Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{!! \App\Models\VisitTransferLegacy\Application::statisticUnderReview()!!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists/accepted') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.application.list", ["accepted"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Accepted Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{!! \App\Models\VisitTransferLegacy\Application::statisticAccepted() !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/application/lists/closed') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.application.list", ["closed"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Closed Applications</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-red">{!! \App\Models\VisitTransferLegacy\Application::statisticClosed() !!}</small>
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
                    <a href="{{ URL::route("adm.visiting.reference.list") }}">
                        <i class="fa fa-bars"></i>
                        <span>All References</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-blue">{!! \App\Models\VisitTransferLegacy\Reference::statisticTotal() !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/pending') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.reference.list", ["pending-submission"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Pending Submission</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-orange">{!! \App\Models\VisitTransferLegacy\Reference::statisticRequested() !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/submitted') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.reference.list", ["submitted"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Submitted References</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-blue">{!! \App\Models\VisitTransferLegacy\Reference::statisticSubmitted() !!}</small>
                        </span>
                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/approval') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.reference.list", ["under-review"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Under Review</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-orange">{!! \App\Models\VisitTransferLegacy\Reference::statisticUnderReview() !!}</small>
                        </span>

                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/approval') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.reference.list", ["accepted"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Accepted</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-green">{!! \App\Models\VisitTransferLegacy\Reference::statisticAccepted() !!}</small>
                        </span>

                    </a>
                </li>

                <li {{ (\Request::is('adm/visit-transfer/reference/approval') ? ' class="active"' : '') }}>
                    <a href="{{ URL::route("adm.visiting.reference.list", ["rejected"]) }}">
                        <i class="fa fa-bars"></i>
                        <span>Rejected</span>

                        <span class="pull-right-container">
                            <small class="label pull-right bg-red">{!! \App\Models\VisitTransferLegacy\Reference::statisticRejected() !!}</small>
                        </span>

                    </a>
                </li>
            </ul>
        </li>

    </ul>
</li>
