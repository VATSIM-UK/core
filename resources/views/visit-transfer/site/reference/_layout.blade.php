@extends('visit-transfer.site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3">
                    @include('components.html.panel_open', [
                        'title' => 'Pending References',
                        'icon' => ['type' => 'fa', 'key' => 'list']
                    ])
                    <ul class="nav nav-pills nav-stacked">
                        <li role="presentation">
                            <a href="{{ route('visiting.landing') }}">Dashboard</a>
                        </li>

                        @foreach(Auth::user()->visit_transfer_referee_pending as $ref)

                            <li role="presentation" {!! (Route::is("visiting.reference.complete") && $reference->id == $ref->id ? "class='active'" : "") !!}>
                                <a href="{{ route('visiting.reference.complete', [$ref->token->code]) }}" class="{{ (Route::is('visiting.reference.complete')  && $reference->id == $ref->id ? 'active' : '') }}">{{ $ref->application->account->name." - ".$ref->application->type_string." ".$ref->application->facility->name }}</a>
                            </li>

                        @endforeach
                    </ul>
                    @include('components.html.panel_close')
                </div>
                <div class="col-md-9">
                    @yield("vt-content")
                </div>
            </div>
        </div>
    </div>
@stop
