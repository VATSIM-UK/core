@can('use-permission', "adm/atc")
    <li class="treeview {{ ((\Request::is('adm/atc*')) ? 'active' : '') }}">
        <a href="#">
            <i class="ion ion-radio-waves"></i> <span>ATC</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">

            @can('use-permission', "adm/atc/endorsement")
                <li {!! (\Request::is('adm/atc/endorsement*') ? ' class="active"' : '') !!}>
                    <a href="{{ URL::route("adm.atc.endorsement.index") }}">
                        <i class="ion ion-document-text"></i>
                        <span>Endorsements</span>
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endcan