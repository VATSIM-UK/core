<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <title>VATSIM United Kingdom Division</title>

    <!-- Styles -->
    <link media="all" type="text/css" rel="stylesheet" href="{{ mix('css/home.css') }}">

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
                <img class="logo-light" src="images/vatsim_uk_logo.png" alt="logo" width="150px">
            </a>
        </div>

        <section class="navbar-mobile">
            <ul class="nav nav-navbar ml-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Welcome</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('site.staff') }}">Staff</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Pilots <span class="arrow"></span></a>
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link" href={{route("site.pilots.landing")}}>Training</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href={{route("site.airports")}}>Airfield Information</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Membership <span class="arrow"></span></a>
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link" href="https://cts.vatsim.uk">CTS</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://community.vatsim.uk">Forum</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://helpdesk.vatsim.uk/">Helpdesk</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="http://community.vatsim-uk.co.uk/downloads">Downloads</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{route("site.community.vt-guide")}}">Visit or Transfer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="https://community.vatsim.uk/files/downloads/file/25-division-policy">Division Policy</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mship.feedback.new') }}">Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="https://www.facebook.com/vatsimuk" target="_blank"><i class="fa fa-facebook"></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="https://twitter.com/vatsimuk" target="_blank"><i class="fa fa-twitter"></i></a>
                    </li>
                    @if(Auth::guard('vatsim-sso')->check())

                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link text-white">{{ $_account->full_name }} <i class="fa fa-user"></i></a>
                    </li>
                    @else
                        <a href="{{ route('login') }}" class="nav-link text-white">Login <i class="fa fa-sign-in"></i></a>
                    @endif
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
                @if(Auth::guard('vatsim-sso')->check() && $_account->hasState('DIVISION'))
                    <h1>Welcome back, {{ $_account->name_first }}!</h1>
                    <p class="lead mt-5 my-0">Did you know you're one of {{ $stats['members_division'] }} members of VATSIM UK?</p>
                    <hr class="w-10 my-7">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('dashboard') }}">Enter</a>
                @elseif(Auth::guard('vatsim-sso')->check())
                    <h1>Welcome to VATSIM UK, {{ $_account->name_first }}!</h1>
                    <p class="lead mt-5 my-0"> Have you considered visiting or transferring to the UK?</p>
                    <p class="lead"><a href="{{ route('visiting.landing') }}" class="text-white">Click here to learn more!</a></p>
                    <hr class="w-10 my-7">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('dashboard') }}">Enter</a>
                @else
                    <h1>Welcome to VATSIM UK!</h1>
                    <p class="lead mt-5"> We pride ourselves in providing regular and high quality air traffic control for our pilots.</p>
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
            VATSIM UK provides air traffic control and a wealth of information for controlling and flying in the United Kingdom on VATSIM. We pride ourselves in providing regular and high quality air traffic control for our pilots. This, combined with our great community, is what makes VATSIM UK such a great place to be. Get involved!
        </p>

        <p>
            To join our great community, simply follow the easy-to-follow steps over at our Join Us page. Whether as a pilot, controller or both, you will receive a warm welcome by our community and will have a great time, whilst making a lot of new friends along the way.
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
<section class="section py-7 text-white bg-img-bottom" style="background-image: url(images/cockpit.jpg)" data-overlay="9">
    <div class="container text-center">

        <div class="row">
            @if(Auth::guard('vatsim-sso')->check())
                <div class="col-6">
                    <h3>Welcome back, {{ $_account->name_first }}!</h3>
                    <small>How've you been?</small>
                </div>

                <div class="col-2 py-4 px-4">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="https://community.vatsim.uk/downloads">Downloads</a>
                </div>

                <div class="col-2 py-4 px-4">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="https://moodle.vatsim.uk">Moodle</a>
                </div>

                <div class="col-2 py-4">
                    <a class="btn btn-xl btn-round btn-primary px-7" href="https://helpdesk.vatsim.uk">Helpdesk</a>
                </div>
            @else
                <div class="col-12 col-sm-6">
                    <h3>Let's get to know eachother...</h3>
                    <small>Login or sign up today for free!</small>
                </div>

                <div class="col-12 col-sm-6">
                    <div class="row">
                        <div class="col-6">
                            <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('login') }}">Login</a>
                        </div>

                        <div class="col-6">
                            <a class="btn btn-xl btn-round btn-primary px-7" href="{{ route('site.join') }}">Join</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>


    </div>
</section>


<!-- Scripts -->
<script src="js/home.js"></script>
<script src="https://unpkg.com/jarallax@1.10/dist/jarallax.min.js"></script>
<script src="https://unpkg.com/jarallax@1.10/dist/jarallax-video.min.js"></script>

</body>
</html>