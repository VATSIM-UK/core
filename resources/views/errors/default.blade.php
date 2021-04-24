@extends('layout', ['shellOnly' => true])

@section('content')
<h1>
    @section('error_code')
        500
    @show
    -
    @section('error_title')Oops! Something went wrong.@show
</h1>
<p>
    @section('error_content')
        <p>
            We will work on fixing that right away.
        </p>
    @show
    <p>
        If you keep experiencing this issue, please let the web services team know by emailing {!! HTML::mailto('web-support@vatsim.uk') !!}.
        @if(Auth::check())
             Meanwhile, you may {{ link_to("/mship/manage/dashboard", "return to the dashboard") }}.
        @endif
    </p>

    @section('error_image')
        <p align='center'>
            {!! HTML::image("/images/error.jpg") !!}
        </p>
    @show

    <h4>Error Detail</h4>
    <pre>Error ID: {{ app('sentry')->getLastEventId() }}</pre>
</p>

@if(app()->bound('sentry') && app('sentry')->getLastEventId())
    <script
    src="https://browser.sentry-cdn.com/6.3.1/bundle.min.js"
    integrity="sha384-Lmmo/x0L5f+PY37NWsRfDV4wUY7ZtKf6LuOumcdJzuA29Mmx62QZX2ceYDjXYtM6"
    crossorigin="anonymous"
    ></script>
    <script>
    Sentry.init({ dsn: 'https://b23b775aaa1a4698ad7649debb154e9a@o578372.ingest.sentry.io/5734564' });
    Sentry.showReportDialog({
        eventId: '{{ app('sentry')->getLastEventId() }}',
        user: {
            email: '{{ auth()->user()->email ?? '' }}',
            name: '{{ auth()->user()->name ?? '' }}'
            }
        });
    </script>
@endif
@stop
