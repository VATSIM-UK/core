<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
        <title>VATSIM-UK Core System</title>

        <!-- CSS -->
        @section('styles')
        {{ HTML::style('http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.css') }}
        {{ HTML::style('http://netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css') }}
        {{ HTML::style('http://netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap-theme.min.css') }}
        {{ HTML::style('assets/style/Standalone/design.css') }}
        {{ HTML::style('http://fonts.googleapis.com/css?family=Yellowtail') }}
        {{ HTML::style('http://fonts.googleapis.com/css?family=Josefin+Slab:600') }}
        {{ HTML::style('http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css') }}
        {{ HTML::style('assets/bootstrap/3/css/summernote.css') }}
        {{ HTML::style('assets/bootstrap/3/css/summernote-bs3.css') }}
        {{ HTML::style('assets/bootstrap/3/css/bootstrap-switch.min.css') }}
        @show

        <!-- Javascript -->
        @section('scripts')
        {{ HTML::script('http://code.jquery.com/jquery-1.9.1.min.js') }}
        {{ HTML::script('assets/jquery/js/jquery.cookie.js') }}
        {{ HTML::script('http://code.jquery.com/ui/1.10.1/jquery-ui.js') }}
        {{ HTML::script('http://netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js') }}
        {{ HTML::script('assets/bootstrap/3/js/summernote.min.js') }}
        {{ HTML::script('assets/bootstrap/3/js/bootstrap-switch.min.js') }}
        @show
    </head>
    <body>
        <div class="container container-header">
            <div class="row">
                <div class="col-md-4 header-left">
                    <p align="left">
                        {{ HTML::image("assets/style/global/images/logo.png") }}
                    </p>
                </div>
                <div class="col-md-8 header-right hidden-xs hidden-sm">
                    <p align="right">
                        {{ HTML::image("assets/style/global/images/slogan.png") }}
                    </p>
                </div>
            </div>
        </div>
        <div class="container" id="mainContainer">
            <div class="container container-menu">

            </div>

            <div class="container container-content">
                <div class="content">
                    <div class="content-inner">
                        @if(!isset($shellOnly) OR !$shellOnly)
                            @section('breadcrumb')
                            <h1>
                                @foreach($_breadcrumb as $_b => $b)
                                @if($b != last($_breadcrumb))
                                <small>{{ ucfirst($b[0]) }}</small>
                                <small><span style='color: black'>&rsaquo;</span></small>
                                @endif
                                @endforeach
                                {{ isset($_pageTitle) ? $_pageTitle : "No Page Title" }}
                            </h1>
                            @show
                        @endif

                        @if(Session::has('error') OR isset($error))
                        <div class="alert alert-danger" role="alert">
                            <strong>Error!</strong> {{ Session::has('error') ? Session::pull("error") : $error }}
                        </div>
                        @endif

                        @if(Session::has('success') OR isset($success))
                        <div class="alert alert-success" role="alert">
                            <strong>Success!</strong> {{ Session::has('success') ? Session::pull("success") : $success }}
                        </div>
                        @endif

                        @yield('content', "No content to display")
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-footer">
            <div class="footer">
                <div class="row">
                    <p>
                        VATSIM-UK &copy; {{ date("Y") }} -
                        {{ HTML::link('http://status.vatsim-uk.co.uk', 'Version '.exec("cd ".base_path()." && git describe --abbrev=0 --tags"), array('target' => '_blank')) }}
                        ({{ gmdate("d/m/y H:i \G\M\T", filemtime(realpath(base_path()."/.git/"))) }})
                        <br align="center">
                        Got a problem? Email us: {{ HTML::link('http://helpdesk.vatsim-uk.co.uk', 'web-support][at][vatsim-uk.co.uk', array('target' => '_blank')) }}
                    </p>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript" language="javascript">
        $(".tooltip_displays").tooltip();
    </script>
</html>
