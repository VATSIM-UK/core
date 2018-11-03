@if($_account->hasPermissionTo("adm/ops"))
    <li class="treeview {{ ((\Request::is('adm/ops*')) ? 'active' : '') }}">
        <a href="#">
            <i class="ion ion-gear-b"></i> <span>Operations</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">

            @if($_account->hasPermissionTo("adm/ops/qstats"))
                <li {!! (\Request::is('adm/atc/qstats*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.ops.qstats.index") }}">
                        <i class="ion ion-document-text"></i>
                        <span>Quarterly Stats</span>
                    </a>
                </li>
            @endif

        </ul>
    </li>
@endif