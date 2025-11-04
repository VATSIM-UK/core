<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <title>VATSIM United Kingdom Division</title>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
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
                            <a class="nav-link" href="https://community.vatsim.uk/files/downloads/category/9-minutes-reports/">Meeting Minutes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="">Policies</a> {{-- Link to Policy tab landing page, to be created --}}
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://helpdesk.vatsim.uk/">Contact Us</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pilots <span class="arrow"></span></a>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link" href="https://ukcp.vatsim.uk/request-a-stand">Request a Stand</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://chartfox.org/">Chartfox</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://cts.vatsim.uk/bookings/calendar.php">ATC Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.operations.sectors') }}">UK Area Sectors</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.pilots.landing') }}">Pilot Training</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.pilots.tfp') }}">The Flying Programme</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('mship.feedback.new') }}">Submit Feedback</a>
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
                            <a class="nav-link" href="{{ route('site.roster.index') }}">Controller Roster</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://docs.vatsim.uk/">Documentation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.atc.endorsements') }}">Raiting Endorsements</a>
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
                            <a class="nav-link" href="{{ route('discord.show') }}">Discord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('site.community.teamspeak') }}">TeamSpeak</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.facebook.com/vatsimuk">Facebook</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.twitter.com/vatsimuk">X</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://www.instagram.com/vatsimuk/">Instagram</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="https://community.vatsim.uk">Forum</a>
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
                            <a class="nav-link" href="https://cts.vatsim.uk/bookings/calendar.php">Booking System</a>
                        </li>
                    </ul>
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