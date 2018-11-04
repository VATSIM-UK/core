@can('use-permission', "adm/mship")
    <li class="treeview {{ ((\Request::is('adm/mship*') && !\Request::is('adm/mship/feedback*')) ? 'active' : '') }}">
        <a href="#">
            <i class="ion ion-person-stalker"></i> <span>Membership</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
            @can('use-permission', "adm/mship/account")
                <li {!! (\Request::is('adm/mship/account*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.mship.account.index") }}"><i
                                class="fa fa-angle-double-right"></i> Accounts List</a>
                </li>
            @endcan
            @can('use-permission', "adm/mship/account/*/bans")
                <li {!! (\Request::is('adm/mship/bans*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.mship.ban.index") }}"><i
                                class="fa fa-angle-double-right"></i> Bans List</a>
                </li>
            @endcan
            @can('use-permission', "adm/mship/staff")
                <li {!! (\Request::is('adm/mship/staff*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.mship.staff.index") }}"><i
                                class="fa fa-angle-double-right"></i> Staff List / Layout</a>
                </li>
            @endcan

            @can('use-permission', "adm/mship/role")
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
            @endcan

            @can('use-permission', "adm/mship/note")
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
            @endcan

        </ul>
    </li>
@endcan