@extends('visittransfer::site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3">
                    {!! HTML::panelOpen("Pending References", ["type" => "fa", "key" => "list"]) !!}
                    <ul class="nav nav-pills nav-stacked">
                        <li role="presentation">
                            {{ link_to_route("visiting.landing", "Dashboard") }}
                        </li>

                        @foreach(Auth::user()->visit_transfer_referee_pending as $ref)

                            <li role="presentation" {!! (Route::is("visiting.reference.complete") && $reference->id == $ref->id ? "class='active'" : "") !!}>
                                {{ link_to_route("visiting.reference.complete", $ref->application->account->name." - ".$ref->application->type_string." ".$ref->application->facility->name, [$ref->token->code], ["class" => (Route::is("visiting.reference.complete")  && $reference->id == $ref->id ? "active" : "")]) }}
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
