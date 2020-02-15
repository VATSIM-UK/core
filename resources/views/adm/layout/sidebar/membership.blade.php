@can('use-permission', "adm/mship")
    <li class="treeview {{ ((\Request::is('adm/mship*') && !\Request::is('adm/mship/feedback*')) ? 'active' : '') }}">
        <a href="#">
            <i class="ion ion-person-stalker"></i> <span>Membership</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
            @can('use-permission', "adm/mship/account/*/bans")
                <li {!! (\Request::is('adm/mship/bans*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.mship.ban.index") }}"><i
                            class="fa fa-angle-double-right"></i> Bans List</a>
                </li>
            @endcan

            @can('use-permission', "adm/mship/role")
                <li {!! (\Request::is('adm/mship/role*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.mship.role.index") }}"><i
                            class="fa fa-angle-double-right"></i> Roles List</a>
                </li>
            @endcan

            @can('use-permission', "adm/mship/permission")
                <li {!! (\Request::is('adm/mship/permission*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.mship.permission.index") }}"><i
                            class="fa fa-angle-double-right"></i> Permissions List</a>
                </li>
            @endcan

        </ul>
    </li>
@endcan
