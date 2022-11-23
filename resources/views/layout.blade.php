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
<script src="{{ mix('js/top-notification.js') }}"></script>

@if(Carbon\Carbon::now()->month == 12 || Carbon\Carbon::now()->dayOfYear < 10)
    <script src="{{ mix('js/snow.js') }}"></script>
@endif

@yield('scripts')
@include('partials/_snow')
</body>
</html>
