@extends('adm.layout', ['shellOnly' => !Auth::check()])

@section('content')
<div class="error-page">
    <h2 class="headline">
        @section('error_code')
        500
        @show
    </h2>
    <div class="error-content">
        <h3><i class="fa fa-warning text-yellow"></i> @section('error_title')Oops! Something went wrong.@show</h3>
        @section('error_content')
        <p>
            We will work on fixing that right away.

        </p>
        @show
        <p>
            If you keep experiencing this issue, please let the web services team know by emailing {!! HTML::mailto('web-support@vatsim.uk') !!}.
            @if(Auth::check())
                 Meanwhile, you may {!! link_to_route("adm.dashboard", "return to the dashboard") !!}.
            @endif
        </p>
    </div>
    @if(Session::has("error_message"))
    <p>
    <pre>
        {{ Session::get("error_message", "No message provided.") }}
        {{ Session::get("error_file", "No file provided.") }}
        {{ Session::get("error_line", "No line provided.") }}
    </pre>
    </p>
    @endif
</div><!-- /.error-page -->
@stop

@section('scripts')
@parent
@stop