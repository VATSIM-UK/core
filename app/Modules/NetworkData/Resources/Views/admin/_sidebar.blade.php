<li class="treeview {{ (\Request::is('adm/network-data*') ? 'active' : '') }}">

    <a href="#">
        <i class="ion ion-gear-b"></i> <span>Network Data</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>

    <ul class="treeview-menu">

        <li {!! (\Request::is('adm/network-data') ? 'class="active"' : '') !!}>
            <a href="{{ URL::route("networkdata.admin.dashboard") }}">
                <i class="ion ion-ios-gear-outline"></i> <span> Dashboard</span>
            </a>
        </li>

    </ul>
</li>