@extends ('layout')

@section('content')
    <div class="row equal">
        <div class="col-md-3">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-book"></i> &thinsp; Getting Started
                </div>
                <div class="panel-body text-center">
                    <a href="{{route("fte.guide")}}">
                        {{ HTML::image('/images/book.png', 'world', array( 'width' => 80, 'height' => 80 )) }}
                    </a>
                    <br><br>
                    Check out this guide for how to get started.
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

    <div class="row">
        @foreach($exercises as $exercise)
            <div class="col-md-{{ 12 / $exercises->count() }}">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-triangle-right"></i> &thinsp; {{ $exercise->name }}</div>
                    <div class="panel-body">
                        @if($exercise->image)
                            <div class="text-center">
                                <img src="{{ $exercise->image->asset() }}" class="img-responsive center-block" alt="{{ $exercise->name }}">
                            </div>
                        @endif
                        <p style="margin-top: 10px;">{{ $exercise->description }}</p>
                        <div class="text-right">
                            <a href="{{ route('fte.exercises', $exercise) }}" class="btn btn-primary">View Details &gt;&gt;</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop
