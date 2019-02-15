<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <title>VATSIM United Kingdom Division</title>

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
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script>
        var touchsupport = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)

        function updateClock() {
            var today = new Date();
            var h = today.getUTCHours();
            var m = today.getUTCMinutes();
            var s = today.getSeconds();
            if (h < 10) {
                h = "0" + h
            }
            if (m < 10) {
                m = "0" + m;
            }
            if (s < 10) {
                s = "0" + s;
            }

            $("#clock").text(h + ":" + m + ":" + s + 'Z');
        }

        function removeAnimations(element) {
            $(element).css("-webkit-animation", "none");
            $(element).css("-moz-animation", "none");
            $(element).css("-ms-animation", "none");
            $(element).css("animation", "none");
        }

        function toggleActive() {
            if ($(".sidebar").hasClass("active")) {
                $(".sidebar").removeClass("active");
            } else {
                $(".sidebar").addClass("active");
                removeAnimations(".sidebar")
            }
        }

        $(function () {
            $("#bookingsbutton").click(() => {
                toggleActive()
            })

            if (!touchsupport) {
                $(".popout-button").addClass("has-hover");
            }

            setInterval('updateClock()', 1000);
        });

        $(document).keyup(function (e) {
            if (e.keyCode === 27 && $('.sidebar').hasClass("active")) $('.sidebar').removeClass("active");
            if (e.keyCode === 37 && !$(".sidebar").hasClass("active")) toggleActive();
            if (e.keyCode === 39 && $(".sidebar").hasClass("active")) toggleActive();
            if (e.keyCode === 66) toggleActive();
        });
    </script>

    <!-- Styles -->
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/home.css') }}">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.2/css/all.css"
          integrity="sha384-/rXc/GQVaYpyDdyxK+ecHPVYJSN9bmVFBvjA/9eOB+pb3F2w2N6fc5qB9Ew5yIns" crossorigin="anonymous">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="images/favicon.png">
    <link rel="icon" href="images/favicon.png">
</head>

<body>

<!-- UK TopNav [START] -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">

        <div class="navbar-left">
            <button class="navbar-toggler" type="button">&#9776;</button>
            <a class="navbar-brand" href="#">
                <img class="logo-light mt-2" src="images/vatsim_uk_logo.png" alt="logo" width="150px">
            </a>
        </div>

        <section class="navbar-mobile">
            <ul class="nav nav-navbar ml-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Home <span class="arrow"></span></a>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.staff') }}">Staff</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="https://community.vatsim.uk/files/downloads/category/4-policy-documents/">Policies</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pilots <span class="arrow"></span></a>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="https://cts.vatsim.uk/bookings/calendar.php">ATC Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="http://www.nats-uk.ead-it.com/public/index.php%3Foption=com_content&task=blogcategory&id=6&Itemid=13.html"
                               target="_blank">Charts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.airports') }}">Airports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.operations.sectors') }}">Area Sectors</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.pilots.landing') }}">Pilot Training</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('mship.feedback.new') }}">Feedback</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Controllers <span class="arrow"></span></a>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.atc.newController') }}">Become a Controller</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://cts.vatsim.uk/home/solo.php">Solo Endorsements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://cts.vatsim.uk/home/validations.php">Special
                                Endorsements</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link"
                               href="https://community.vatsim.uk/files/downloads/category/4-policy-documents/">Regulations
                                and Policies</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('visiting.landing') }}">Visit / Transfer</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Community <span class="arrow"></span></a>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="https://community.vatsim.uk">Forum</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.community.teamspeak') }}">TeamSpeak / Slack</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.facebook.com/vatsimuk">Facebook</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.twitter.com/vatsimuk">Twitter</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Events <span class="arrow"></span></a>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="https://cts.vatsim.uk/bookings/calendar.php">Calendar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.marketing.live-streaming') }}">Live Streams</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('mship.feedback.new') }}">Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://helpdesk.vatsim.uk">Contact Us</a>
                </li>
                <a href="{{ route('login') }}" class="nav-link text-white"><i class="fas fa-user"></i></a>
            </ul>
        </section>

    </div>
</nav>
<!-- UK TopNav [END] -->

<!-- Sidebar Content [START] -->
<div class="sidebar">
    <div class="window">
        <div class="content">
            <div class="header">
                <h2>Today's Bookings</h2>
                <p>All times are represented in Zulu. (Currently <b><span id="clock"></span></b> )</p>
            </div>
            <div class="data">
                <ul>
                    @forelse ($bookings as $booking)
                        <li class='booking'>
                            <a href="https://cts.vatsim.uk/bookings/bookinfo.php?cb={{ $booking['id'] }}"
                               target="_blank">
                                <div class="icon">
                                    @if($booking['type'] == 'EX')
                                        <i class="fas fa-exclamation"></i>
                                    @elseif($booking['type'] == 'ME')
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    @elseif($booking['type'] == 'BK')
                                        <i class="fas fa-headset"></i>
                                    @endif
                                </div>
                                <div>
                                    <b>{{ $booking['position'] }}
                                        @if($booking['type'] == 'EX')
                                            (E)
                                        @elseif($booking['type'] == 'ME')
                                            (M)
                                        @endif
                                    </b><br/>
                                    {{ $booking['member']['name'] }}
                                    @if($booking['member']['id'])
                                        ({{ $booking['member']['id'] }})
                                    @endif
                                    <br/>
                                    {{$booking['from']}}z - {{$booking['to']}}z<br/>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li>There are no bookings today. <i class="far fa-tired"></i></li>
                    @endforelse
                </ul>
                <div class="spacer"></div>
            </div>
            <div class="footer">
                @if (count($bookings) > 10)
                    <span><i>Keep Scrolling</i></span>
                @else
                    <span>&nbsp;</span>
                @endif
                <a class="btn btn-l btn-round btn-primary px-7" href="https://cts.vatsim.uk/bookings/calendar.php">View
                    Full Calendar</a>
            </div>
        </div>
    </div>
    <div class="icons">
        <div class="icon popout-button">
            <span id="bookingsbutton"><i class="fas fa-headset"></i></span>
        </div>
    </div>
</div>
<!-- Sidebar Content [END] -->

<!-- UK Header [START] -->
<header class="header text-white h-fullscreen pb-5" data-jarallax-video="mp4:videos/ctp.mp4" data-overlay="5">
    <div class="overlay opacity-55" style="background-color: #17375E"></div>

    <div class="container">
        <div class="row align-items-center h-100">

            <div class="col-md-8 mx-auto text-center py-8 flex-grow">
                @if(currentUserHasAuth() && $_account->hasState('DIVISION'))
                    <h1>Welcome back, {{ $_account->name_first }}!</h1>
                    <p class="lead mt-5 my-0">Did you know you're one of {{ $stats['members_division'] }} members of
                        VATSIM UK?</p>
                    <hr class="w-10 my-7">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('dashboard') }}">Enter</a>
                @elseif(currentUserHasAuth())
                    <h1>Welcome to VATSIM UK, {{ $_account->name_first }}!</h1>
                    <p class="lead mt-5 my-0"> Have you considered visiting or transferring to the UK?</p>
                    <p class="lead"><a href="{{ route('visiting.landing') }}" class="text-white">Click here to learn
                            more!</a></p>
                    <hr class="w-10 my-7">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('dashboard') }}">Enter</a>
                @else
                    <h1>Welcome to VATSIM UK!</h1>
                    <p class="lead mt-5"> We pride ourselves in providing regular and high quality air traffic control
                        for our pilots.</p>
                    <hr class="w-10 my-7">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('site.join') }}">Join Us!</a>
                @endif
            </div>

        </div>
    </div>
</header>
<!-- UK Header [END] -->

<section class="section bg-gray overflow-hidden">
    <!-- UK Welcome [START] -->
    <div class="container">

        <h1 class="text-primary">Welcome!</h1><br>

        <p>
            VATSIM UK provides air traffic control and a wealth of information for controlling and flying in the United
            Kingdom on VATSIM. We pride ourselves in providing regular and high quality air traffic control for our
            pilots. This, combined with our great community, is what makes VATSIM UK such a great place to be. Get
            involved!
        </p>

        <p>
            To join our great community, simply follow the easy-to-follow steps over at our Join Us page. Whether as a
            pilot, controller or both, you will receive a warm welcome by our community and will have a great time,
            whilst making a lot of new friends along the way.
        </p>
        <br>

        <p class="text-right text-light">Simon Irvine <br/> VATSIM UK Division Director</p>

    </div>
    <!-- UK Welcome [END] -->

    <!-- Upcoming Event [START] -->
    <div class="container">

        <h1 class="text-primary">Next Event</h1><br>

        <p>
            @if($nextEvent)
                {!! $nextEvent !!}
            @else
                No upcoming events.
            @endif
        </p>

    </div>
    <!-- Upcoming Event [END] -->
</section>

<!-- UK User Welcome [START] -->
<section class="section py-7 text-white bg-img-bottom" style="background-image: url(images/cockpit.jpg)"
         data-overlay="9">
    <div class="container text-center">

        <div class="row">
            @if(currentUserHasAuth())
                <div class="col-12 col-lg-4">
                    <h3>Welcome back, {{ $_account->name_first }}!</h3>
                    <small>How've you been?</small>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="row">
                        <div class="col-12 col-md-4 mb-3">
                            <a class="btn btn-xl btn-round btn-primary px-7"
                               href="https://community.vatsim.uk/downloads">Downloads</a>
                        </div>

                        <div class="col-12 col-md-4 mb-3">
                            <a class="btn btn-xl btn-round btn-primary px-7" href="https://moodle.vatsim.uk">Moodle</a>
                        </div>

                        <div class="col-12 col-md-4 mb-3">
                            <a class="btn btn-xl btn-round btn-primary px-7"
                               href="https://helpdesk.vatsim.uk">Helpdesk</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-12 col-sm-6">
                    <h3>Let's get to know eachother...</h3>
                    <small>Login or sign up today for free!</small>
                </div>

                <div class="col-12 col-sm-6">
                    <div class="row">
                        <div class="col-6">
                            <a class="btn btn-l btn-round btn-primary px-7" href="{{ route('login') }}">Login</a>
                        </div>

                        <div class="col-6">
                            <a class="btn btn-l btn-round btn-primary px-7" href="{{ route('site.join') }}">Join</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>


    </div>
</section>

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

<!-- Scripts -->

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
<script src="{{ mix('js/home.js') }}"></script>
<script src="https://unpkg.com/jarallax@1.10/dist/jarallax.min.js"></script>
<script src="https://unpkg.com/jarallax@1.10/dist/jarallax-video.min.js"></script>

</body>
</html>
