<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <title>VATSIM United Kingdom Division</title>

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
    <script src="https://slug.vatsim.uk/script.js" data-site="HQWHPBQX" data-included-domains="vatsim.uk,www.vatsim.uk" defer></script>

    <!-- Styles -->
    @vite('resources/assets/sass/home.scss')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.2/css/all.css"
          integrity="sha384-/rXc/GQVaYpyDdyxK+ecHPVYJSN9bmVFBvjA/9eOB+pb3F2w2N6fc5qB9Ew5yIns" crossorigin="anonymous">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="images/favicon.png">
    <link rel="icon" href="images/favicon.png">
</head>

<body>
@include('components.top-notification')
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
                               href="{{ route('site.airports') }}"
                               target="_blank">Charts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.airports') }}">Airports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.pilots.stands') }}">Stand Guide</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.operations.sectors') }}">Area Sectors</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.pilots.landing') }}">Pilot Training</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.pilots.tfp') }}">Flying Programme</a>
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
                            <a class="nav-link" href="{{ config('services.docs.url') }}">Documentation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.roster.index') }}">Controller Roster</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.atc.heathrow') }}">Heathrow Endorsements</a>
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
                            <a class="nav-link" href="{{ route('site.community.teamspeak') }}">TeamSpeak</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('discord.show') }}">Discord</a>
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
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('mship.feedback.new') }}">Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://helpdesk.vatsim.uk">Contact Us</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('landing') }}" class="nav-link">
                        <i class="fas fa-user text-white d-mobile-none"></i>
                        <span class="d-tablet-none">Login</span>
                    </a>
                </li>
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
                    @foreach($events as $event)
                        <li class='booking event-booking'>
                            @if($event->thread)
                                <a href="{{$event->thread}}"
                                   target="_blank">
                            @else
                                <span>
                            @endif
                                <div class="icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div>
                                    <b>{{$event->event}}</b>
                                    <br/>
                                    {{$event->from}}z - {{$event->to}}z<br/>
                                </div>
                            @if($event->thread)
                                </a>
                            @else
                                </span>
                            @endif
                        </li>
                        @if($loop->last)
                            <hr class="mt-2 mb-2">
                        @endif
                    @endforeach
                    @foreach ($bookings as $booking)
                        <li class='booking'>
                            <a href="https://cts.vatsim.uk/bookings/bookinfo.php?cb={{ $booking->id }}"
                               target="_blank">
                                <div class="icon">
                                    @if($booking->isExam())
                                        <i class="fas fa-exclamation"></i>
                                    @elseif($booking->isMentoring())
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    @elseif($booking->isMemberBooking())
                                        <i class="fas fa-headset"></i>
                                    @endif
                                </div>
                                <div>
                                    <b>{{ $booking['position'] }}
                                        @if($booking->isExam())
                                            (E)
                                        @elseif($booking->isMentoring())
                                            (M)
                                        @endif
                                    </b><br/>
                                    @if($booking['member']['id'])
                                        {{ $booking['member']['id'] }}
                                    @endif
                                    <br/>
                                    {{$booking['from']}}z - {{$booking['to']}}z<br/>
                                </div>
                            </a>
                        </li>
                    @endforeach
                    @if($bookings->count() == 0 && $events->count() == 0)
                        <li>There are no bookings today. <i class="far fa-tired"></i></li>
                    @endif
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
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('landing') }}">Enter</a>
                @elseif(currentUserHasAuth())
                    <h1>Welcome to VATSIM UK, {{ $_account->name_first }}!</h1>
                    <p class="lead mt-5 my-0"> Have you considered visiting or transferring to the UK?</p>
                    <p class="lead"><a href="{{ route('visiting.landing') }}" class="text-white">Click here to learn
                            more!</a></p>
                    <hr class="w-10 my-7">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('landing') }}">Enter</a>
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
            VATSIM UK is the place to be for any members who regularly fly or wish to control on the VATSIM Network in
            the United Kingdom. We are known across the virtual globe for the quality of our ATC training, and our
            airports consistently rank amongst the busiest of those on the Network. We have developed a close-knit
            community of flight simulation enthusiasts - whether you are a student longing for a career in aviation, a
            retired professional looking for a new hobby or simply someone who loves flying and wants to meet other
            like-minded individuals, I am sure you will feel at home within our Division.
        </p>

        <p>
            I would encourage all members, particularly those at the beginning of their VATSIM journey, to join our
            Discord server and take advantage of the wealth of knowledge within our community. We also offer a bespoke
            ‘Flying Programme’ as a natural successor to the P0 rating, where newer members are able to receive
            one-on-one, live training with our experienced flight instructors. For members interested in becoming a
            virtual air traffic controller, our website contains a useful guide to help you understand the training
            process and requirements.
        </p>

        <p>
            Joining VATSIM UK has given me everything from new friends and memorable trips, to career advice and flying
            tips. I hope that everyone is able to benefit from our incredible community just as much as I have and I can
            guarantee that, regardless of your reason for joining VATSIM UK, you will be met with a warm welcome.
        </p>
        <br>

        <p class="text-right text-light">Ben Wright <br/> VATSIM UK Division Director</p>

    <!-- UK Welcome [END] -->

    <!-- Upcoming Event [START] -->

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
<div class="overlay opacity-55" style="background-color: #17375E"></div>
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

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jarallax/1.10.3/jarallax.min.js" integrity="sha512-1RIeczLHpQNM864FPmyjgIOPQmljv9ixHg5J1knRhTApLpvxqA0vOTxgGF89/DpgZIAXRCn9dRiakPjyTUl9Rg==" crossorigin="anonymous"></script>
@vite('resources/assets/js/home.js')
@vite('resources/assets/js/top-notification.js')

<script src="https://unpkg.com/jarallax@1.10/dist/jarallax.min.js"></script>
<script src="https://unpkg.com/jarallax@1.10/dist/jarallax-video.min.js"></script>
@include('partials/_snow')
</body>
</html>
