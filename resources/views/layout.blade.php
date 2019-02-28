<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    @if(isset($_pageTitle))
        <title>VATSIM UK | {{ $_pageTitle }}</title>
    @else
        <title>VATSIM UK | United Kingdom Division of VATSIM.net</title>
    @endif

    <!--BugSnagScript-->
    <script src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-3.min.js"
            data-apikey="b3be4a53f2e319e1fa77bb3c85a3449d"
            data-releasestage="{{ env('APP_ENV') }}"></script>
    <script type="text/javascript">
        Bugsnag.notifyReleaseStages = ["staging", "production"];

        @if(Auth::check())
            Bugsnag.user = {
            id: {{ Auth::user()->id }},
            name: "{{ Auth::user()->name }}",
            email: "{{ Auth::user()->email }}"
        };
        @endif
    </script>

    <!-- CSS -->
    <link media="all" type="text/css" rel="stylesheet" href="//fonts.googleapis.com/css?family=Yellowtail">
    <link media="all" type="text/css" rel="stylesheet" href="//fonts.googleapis.com/css?family=Josefin+Slab:600">
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/app-all.css') }}">
    {{-- Dynamic Style --}}
    <style type="text/css">
        .banner{
            background: url({{$_bannerUrl}}) no-repeat 50%;
            background-size: cover;
        }
    </style>

    @yield('styles')
</head>
<body>
@include('components.nav')
<div class="container-fluid">
    <div class="header_container">
        <div id="banner" class="banner hidden-xs hidden-sm"></div>
        
        <div class="breadcrumb_outer_container hidden-xs hidden-sm">
            <div class="breadcrumb_container">
                <div class="breadcrumb_content_left">
                    @if(Auth::check())
                        <span>You are logged in.</span>
                    @else
                        <span>You are not logged in.</span>
                    @endif
                </div>
                <div class="breadcrumb_content_right">
                    VATSIM UK /
                    @if(isset($_breadcrumb))
                        @foreach($_breadcrumb as $bread)
                        <a href="{{ $bread['uri'] }}">{{ $bread['name'] }}</a> /
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="banner_breadcrumb_spacer visible-xs visible-sm"></div>

    </div>

    <div class="page_content">

        @include('components.errors')

        <div class="container-fluid">
            @yield('content', "No content to display")
        </div>

    </div>

</div>

<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha384-rY/jv8mMhqDabXSo+UCggqKtdmBfd3qC2/KvyTDNQ6PcUJXaxK1tMepoQda4g5vB" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="{{ mix('js/app-all.js') }}"></script>

<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-13128412-6', 'auto');
    ga('send', 'pageview');

</script>

@if(App::environment('production'))
<script type="text/javascript">
    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    (function () {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/57bb3bfca767d83b45e79605/1aqq3gev7';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();

    @if(Auth::check())
        Tawk_API.visitor = {
        name: "{{ Auth::user()->name }} ({{ Auth::user()->id }})",
        email: "{{ Auth::user()->email }}"
    };
    @endif
</script>
@endif

@yield('scripts')

</body>
</html>
