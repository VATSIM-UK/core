@can('use-permission', 'adm/smartcars')
    <li class="treeview {{ Request::is('adm/smartcars*') ? 'active' : '' }}">
        <a href="#">
            <i class="ion ion-paper-airplane"></i> <span>smartCARS</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
            <li class="treeview {{ Request::is('adm/smartcars/configure*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-cog"></i> Configuration
                    <span class="pull-right-container">
                                  <i class="fa fa-angle-left pull-right"></i>
                                </span>
                </a>
                <ul class="treeview-menu">

                    @can('use-permission', 'adm/smartcars/aircraft')
                        <li {!! Request::is('adm/smartcars/configure/aircraft*') ? ' class="active"' : '' !!}>
                            <a href="{{ URL::route('adm.smartcars.aircraft.index') }}">
                                <i class="fa fa-plane"></i>
                                <span>Aircraft</span>
                            </a>
                        </li>
                    @endcan

                    @can('use-permission', 'adm/smartcars/airports')
                        <li {!! Request::is('adm/smartcars/configure/airports*') ? ' class="active"' : '' !!}>
                            <a href="{{ URL::route('adm.smartcars.airports.index') }}">
                                <i class="fa fa-road"></i>
                                <span>Airports</span>
                            </a>
                        </li>
                    @endcan

                    @can('use-permission', 'adm/smartcars/exercises')
                        <li {!! Request::is('adm/smartcars/configure/exercises*') ? ' class="active"' : '' !!}>
                            <a href="{{ URL::route('adm.smartcars.exercises.index') }}">
                                <i class="fa fa-pencil-square-o"></i>
                                <span>Exercises</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>

            @can('use-permission', 'adm/smartcars/flights')
                <li {!! Request::is('adm/smartcars/flights*') ? ' class="active"' : '' !!}>
                    <a href="{{ URL::route('adm.smartcars.flights.index') }}">
                        <i class="fa fa-fighter-jet"></i>
                        <span>Member Flights</span>
                    </a>
                </li>
            @endcan

        </ul>
    </li>
@endcan