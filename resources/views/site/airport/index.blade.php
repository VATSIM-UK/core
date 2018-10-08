@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-plane"></i> UK Airfields</div>
                <div class="panel-body">
                    @if(!empty($airports->all()))
                        <div class="col-md-6 text-center">
                            <ul class="list-unstyled">
                                @foreach($airports->first() as $airport)
                                    <li>@if($airport->major)<strong>@endif<a href="{{route('site.airport.view', $airport->icao)}}">{{$airport->name}} ({{$airport->icao}} / {{$airport->iata}})</a>@if($airport->major)</strong>@endif</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-6 text-center">
                            <ul class="list-unstyled">
                                @foreach($airports->last() as $airport)
                                    <li>@if($airport->major)<strong>@endif<a href="{{route('site.airport.view', $airport->icao)}}">{{$airport->name}} ({{$airport->icao}} / {{$airport->iata}})</a>@if($airport->major)</strong>@endif</li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="col-md-12 text-center">
                            <strong>No airports to display</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
