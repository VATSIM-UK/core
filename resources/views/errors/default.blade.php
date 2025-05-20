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
        If you keep experiencing this issue, please let the Technology team know by emailing <a href="mailto:technology-support@vatsim.uk">technology-support@vatsim.uk</a>.
        @if(Auth::check())
             Meanwhile, you may <a href="/mship/manage/dashboard">return to the dashboard</a>.
        @endif
    </p>

    @section('error_image')
        <p align='center'>
            <img src="{{ asset('/images/error.jpg') }}" alt="Error Image">
        </p>
    @show
</p>
@stop
