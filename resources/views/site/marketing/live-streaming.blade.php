@extends('layout')

@section('content')

    <div class="row">
        <div class="col-lg-4 col-lg-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-record-vinyl"></i> &thinsp; Live Streaming
                </div>
                <div class="panel-body">
                    <p>Our live streaming schedule will let you know where/when the next event is taking place. Watch in
                        YouTube directly to access other features including chat.</p>
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="center-block embed-responsive-item" allowfullscreen="" frameborder="0"
                                height="400" kwframeid="7"
                                src="https://www.youtube.com/embed/live_stream?channel=UC_QUtHZ0WBG_I8dj9_k-oQw"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-calendar"></i> &thinsp; Upcoming Live Streams
                </div>
                <div class="panel-body">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="center-block" frameborder="0" kwframeid="8"
                                scrolling="no"
                                src="https://calendar.google.com/calendar/b/2/embed?showTitle=0&amp;showNav=0&amp;showPrint=0&amp;showTabs=0&amp;showCalendars=0&amp;height=800&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=vatsim-uk.co.uk_uj5ct8s30fhaar1tqa4vqr4ljs%40group.calendar.google.com&amp;color=%23875509&amp;ctz=Europe%2FLondon"
                                style="border: 0" height="800"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

    </div>

    <div class="row">

    </div>

@stop
