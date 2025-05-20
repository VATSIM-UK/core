@extends('visit-transfer.site._layout')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="row">
                <div class="col-md-3">
                    @include('components.html.panel_open', [
                        'title' => 'Applications',
                        'icon' => ['type' => 'fa', 'key' => 'list'],
                        'attr' => []
                    ])
                    <ul class="nav nav-pills nav-stacked">
                        <li role="presentation">
                            <a href="{{ route('visiting.landing') }}">Dashboard</a>
                        </li>

                        @foreach(Auth::user()->visitTransferApplications as $app)

                            <li role="presentation" {!! (Route::is("visiting.application.view") && $application->id == $app->id ? "class='active'" : "") !!}>
                                <a href="{{ route('visiting.application.view', [$app->public_id]) }}" class="{{ (Route::is('visiting.application.view')  && $application->id == $app->id ? 'active' : '') }}">#{{ $app->public_id }} - {{ $app->type_string }} {{ $app->facility_name }}</a>
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
