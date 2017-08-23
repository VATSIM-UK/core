@extends ('layout')

@section('content')

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
                    <a href="{{ route('fte.exercises', 1) }}" class="btn btn-primary">View Details >></a>
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
                    <a href="{{ route('fte.exercises', 1) }}" class="btn btn-primary">View Details >></a>
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
                    <a href="{{ route('fte.exercises', 1) }}" class="btn btn-primary">View Details >></a>
                </div>
            </div>
        </div>
    </div>

@stop
