@extends('visittransfer::site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3">
                    {!! HTML::panelOpen("Applications", ["type" => "fa", "key" => "list"]) !!}
                    <ul class="nav nav-pills nav-stacked">
                        <li role="presentation">
                            {{ link_to_route("visiting.landing", "Dashboard") }}
                        </li>

                        @foreach(Auth::user()->visitTransferApplications as $app)

                            <li role="presentation" {!! (Route::is("visiting.application.view") && $application->id == $app->id ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.application.view", "#".$app->public_id." - ".$app->type_string." ".$app->facility_name, [$app->public_id], ["class" => (Route::is("visiting.application.view")  && $application->id == $app->id ? "active" : "")]) }}
                            </li>

                        @endforeach
                    </ul>
                    {!! HTML::panelClose() !!}
                </div>
                <div class="col-md-9">
                    @yield("vt-content")
                </div>
            </div>
        </div>
    </div>
@stop
