@can('use_permission', "adm/dashboard")
    <li {!! (\Request::is('adm/dashboard*') ? ' class="active"' : '') !!}>
        <a href="{{ URL::route("adm.dashboard") }}">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
    </li>
@endcan