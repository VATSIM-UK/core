@extends ('layout')

@section('content')
    <div class="col-md-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-globe"></i> &thinsp; Map
            </div>
            <div class="panel-body text-center">
                <a href="{{route("fte.map")}}">
                    {{ HTML::image('/images/world.png', 'world', array( 'width' => 80, 'height' => 80 )) }}
                </a>
                <br><br>
                View map of all flights in progress.
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; Flight Training Exercises
            </div>
            <div class="panel-body">
                <div class="">
                    Fancy something different? VATSIM UK is proud to announce the launch of Flight Training Exercises –
                    the new
                    way to learn and have fun!<br/><br/>
                    Choose one of 3 launch exercises and take flight discovering the South East of the UK
                    and much more. To get started click on one of the exercises below and follow the instructions
                    provided.<br/><br/>
                    If you have any questions please contact the Pilot Training Department via the Helpdesk
                    ({{ HTML::link('https://helpdesk.vatsim.uk/','click here',array("target"=>"_blank")) }}).
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-time"></i> &thinsp; Past Flights
            </div>
            <div class="panel-body text-center">
                <a href="{{route("fte.history")}}">
                    {{ HTML::image('/images/history.png', 'history', array( 'width' => 80, 'height' => 80 )) }}
                </a>
                    <br><br>
                    View flight history.
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 hidden-xs">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-triangle-right"></i> &thinsp; VFR 1 - Road Trip
            </div>
            <div class="panel-body">
                {{ HTML::image('/images/roadtrip.jpg', 'roadtrip', array( 'width' => '100%', 'height' => '50px' )) }}
                <br>
                <br>
                Time to go on a road trip! Pack your backs and go for ride along the M26 and M20 cruising alongside the
                North
                Downs until reaching the sprawling town of Ashford, then route south tracking the LYD VOR until reaching
                the
                seaside village of Lydd. Don’t forget to avoid the danger areas and nuclear power station!
                <br>
                <div class="text-right">
                    <a href="{{ route('fte.exercise', 1) }}" class="btn btn-primary">View Details >></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 hidden-xs">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-triangle-right"></i> &thinsp; VFR 2 - King of the
                Castle
            </div>
            <div class="panel-body">
                {{ HTML::image('/images/castle.jpg', 'castle', array( 'width' => '100%', 'height' => '50px' )) }}
                <br>
                <br>
                Are you the King of the Castle? Route along the south coast visiting Hastings Castle, one of the most
                iconic battle
                grounds of Britain's past, Pevensey Castle and Lewes Castle. Your trip will end with a visit to the
                seaside town of
                Brighton before landing into Shoreham.<br>
                <br>
                <div class="text-right">
                    <a href="{{ route('fte.exercise', 1) }}" class="btn btn-primary">View Details >></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 hidden-xs">
        <div class="panel panel-ukblue">
            <div class="panel-heading"><i class="glyphicon glyphicon-triangle-right"></i> &thinsp; VFR 3 - St
                Catherine's Point
            </div>
            <div class="panel-body">
                {{ HTML::image('/images/isleofwight.jpg', 'isleofwight', array( 'width' => '100%', 'height' => '50px' )) }}
                <br>
                <br>
                Time to venture over the water! A short trip routing westbound along the coast, and a small hop across
                the
                water will lead you to the southern tip of the Isle of Wight. After admiring the views you're routing
                continues up
                towards the town of Bournemouth.<br>
                <br>
                <div class="text-right">
                    <a href="{{ route('fte.exercise', 1) }}" class="btn btn-primary">View Details >></a>
                </div>
            </div>
        </div>
    </div>
@stop
