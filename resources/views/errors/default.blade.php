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
</p>
@stop
