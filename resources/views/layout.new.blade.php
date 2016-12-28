<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <title>VATSIM UK | United Kingdom Division of VATSIM.net</title>

    <!--BugSnagScript-->
    <script src="//d2wy8f7a9ursnm.cloudfront.net/bugsnag-3.min.js"
            data-apikey="b3be4a53f2e319e1fa77bb3c85a3449d"
            data-releasestage="{{ env('APP_ENV') }}">
        Bugsnag.notifyReleaseStages = ["staging", "production"];
        Bugsnag.user = {
            id: {{ Auth::user()->id }},
            name: "{{ Auth::user()->name }}",
            email: "{{ Auth::user()->email }}"
        };
    </script>

    <!-- CSS -->
    {!! HTML::style('//fonts.googleapis.com/css?family=Yellowtail') !!}
    {!! HTML::style('//fonts.googleapis.com/css?family=Josefin+Slab:600') !!}
    {!! HTML::style(elixir("css/app-all.css")) !!}
</head>
<body>
<div class="container-fluid">

    <div class="header_container">

        <div class="nav_upper_container navbar-fixed-top">
            <div class="logo_container">
                {!! HTML::image("build/css/images/vatsim_uk_logo.png", "UK Logo", ["align" => "left", "height" => "70%"]) !!}
            </div>
        </div>

        <div class="banner">
        </div>

        <div class="breadcrumb_container">
            <div class="breadcrumb_content">
                <a href="#">VATSIM UK</a>  /  Home
            </div>
        </div>
    </div>

    <div class="page_content">
        <div class="row">
            <div class="col-md-5">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> &thinsp; General Airport Information</div>
                    <div class="panel-body">

                        <!-- Content Of Panel [START] -->
                        <!-- Top Row [START] -->
                        <div class="row">

                            <div class="col-xs-5">
                                <b>NAME</b>
                                <br/>
                                LONDON GATWICK
                            </div>

                            <div class="col-xs-5">
                                <b>ICAO</b>
                                <br/>
                                EGKK
                            </div>

                            <div class="col-xs-2">
                                <b>IATA</b>
                                <br/>
                                LGW
                            </div>

                        </div>
                        <!-- Top Row [START] -->
                        <br/>
                        <!-- Second Row [START] -->
                        <div class="row">

                            <div class="col-xs-5">
                                <b>ELEVATION</b>
                                <br/>
                                202ft
                            </div>

                            <div class="col-xs-5">
                                <b>CO-ORDINATES</b>
                                <br/>
                                51.085300, 0.001125
                            </div>

                            <div class="col-xs-2">
                                <b>FIR</b>
                                <br/>
                                EGTT
                            </div>

                        </div>
                        <!-- Second Row [END] -->
                        <!-- Content Of Panel [END] -->

                    </div>
                </div>

                <br/>

                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-cloud"></i> &thinsp; Latest Aerodrome Weather</div>
                    <div class="panel-body">

                        <!-- Content Of Panel [START] -->
                        <!-- Top Row [START] -->
                        <div class="row">

                            <div class="col-xs-5">
                                <b>DATE</b>
                                <br/>
                                30/05/2015
                            </div>

                            <div class="col-xs-4">
                                <b>TIME</b>
                                <br/>
                                2250z
                            </div>

                            <div class="col-xs-3">
                                <b>SURFACE WIND</b>
                                <br/>
                                220&deg; 15KTS
                            </div>

                        </div>
                        <!-- Top Row [START] -->
                        <br/>
                        <!-- Second Row [START] -->
                        <div class="row">

                            <div class="col-xs-12">
                                <b>SKY CONDITIONS</b>
                                <br/>
                                CAVOK
                            </div>

                        </div>
                        <!-- Second Row [END] -->
                        <br/>
                        <!-- Third Row [START] -->
                        <div class="row">

                            <div class="col-xs-5">
                                <b>TEMPERATURE</b>
                                <br/>
                                +05&deg;
                            </div>

                            <div class="col-xs-4">
                                <b>DUE POINT</b>
                                <br/>
                                +04&deg;
                            </div>

                            <div class="col-xs-3">
                                <b>QNH</b>
                                <br/>
                                1011hPa
                            </div>

                        </div>
                        <!-- Third Row [END] -->
                        <br/>
                        <!-- Fourth Row [START] -->
                        <div class="row">

                            <div class="col-xs-12">
                                <b>RAW</b>
                                <br/>
                                EGKK 292250z 22006KT CAVOK 05/04 Q1011
                            </div>

                        </div>
                        <!-- Fourth Row [END] -->
                        <!-- Content Of Panel [END] -->

                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-road"></i> &thinsp; Runway Information</div>
                    <div class="panel-body">


                        <!-- Runway 1 [START]-->
                        <div class="runway_container">
                            <div class="runway_name"><span class="runway_bg_fill">Runway <b>08R</b></span></div>

                            <div class="runway_info">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <b>MAGNETIC BEARING</b>
                                        <br/>
                                        079&deg;
                                    </div>

                                    <div class="col-xs-3">
                                        <b>DIMENSIONS</b>
                                        <br/>
                                        3316m x 46m
                                    </div>

                                    <div class="col-xs-3">
                                        <b>SURFACE</b>
                                        <br/>
                                        ASPHALT
                                    </div>

                                    <div class="col-xs-3">
                                        <b>ILS FREQUENCY</b>
                                        <br/>
                                        110.90
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Runway 1 [END]-->
                        </br>
                        <!-- Runway 2 [START]-->
                        <div class="runway_container runay_active">
                            <div class="runway_name">
                                        <span class="runway_bg_fill">
                                            Runway <b>26L</b>
                                            &thinsp;
                                            <span class="label label-success">ACTIVE</span>
                                            &thinsp;
                                            <span class="label label-info">12KTS HEAD WIND | 9KTS LEFT WIND</span>
                                        </span>
                            </div>

                            <div class="runway_info">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <b>MAGNETIC BEARING</b>
                                        <br/>
                                        259&deg;
                                    </div>

                                    <div class="col-xs-3">
                                        <b>DIMENSIONS</b>
                                        <br/>
                                        3316m x 46m
                                    </div>

                                    <div class="col-xs-3">
                                        <b>SURFACE</b>
                                        <br/>
                                        ASPHALT
                                    </div>

                                    <div class="col-xs-3">
                                        <b>ILS FREQUENCY</b>
                                        <br/>
                                        110.90
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Runway 2 [END]-->
                        </br>
                        <!-- Runway 3 [START]-->
                        <div class="runway_container">
                            <div class="runway_name"><span class="runway_bg_fill">Runway <b>08L</b></span></div>

                            <div class="runway_info">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <b>MAGNETIC BEARING</b>
                                        <br/>
                                        079&deg;
                                    </div>

                                    <div class="col-xs-3">
                                        <b>DIMENSIONS</b>
                                        <br/>
                                        2565m x 45m
                                    </div>

                                    <div class="col-xs-3">
                                        <b>SURFACE</b>
                                        <br/>
                                        ASPHALT
                                    </div>

                                    <div class="col-xs-3">
                                        <b>ILS FREQUENCY</b>
                                        <br/>
                                        N/A
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Runway 3 [END]-->
                        </br>
                        <!-- Runway 4 [START]-->
                        <div class="runway_container">
                            <div class="runway_name"><span class="runway_bg_fill">Runway <b>26R</b></span></div>

                            <div class="runway_info">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <b>MAGNETIC BEARING</b>
                                        <br/>
                                        259&deg;
                                    </div>

                                    <div class="col-xs-3">
                                        <b>DIMENSIONS</b>
                                        <br/>
                                        2565m x 45m
                                    </div>

                                    <div class="col-xs-3">
                                        <b>SURFACE</b>
                                        <br/>
                                        ASPHALT
                                    </div>

                                    <div class="col-xs-3">
                                        <b>ILS FREQUENCY</b>
                                        <br/>
                                        N/A
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Runway 4 [END]-->

                    </div>
                </div>
            </div>

        </div>

        <br/>

        <div class="row">
            <div class="col-lg-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-menu-up"></i> &thinsp; Live Departure Board</div>
                    <div class="panel-body">


                        <table class="table">

                            <tr>
                                <th>CALLSIGN</th>
                                <th>PILOT</th>
                                <th>DESTINATION</th>
                                <th>STATUS</th>
                            </tr>

                            <tr>
                                <td>EZY232</td>
                                <td>JOE CLIFFORD</td>
                                <td>PALMA</td>
                                <td><span class="label label-warning">TAXYING</span></td>
                            </tr>

                            <tr>
                                <td>BA29G</td>
                                <td>ANTHONY LAWRENCE</td>
                                <td>BOSTON</td>
                                <td><span class="label label-primary">BORDING</span></td>
                            </tr>

                            <tr>
                                <td>EZY27J</td>
                                <td>BARRIE JOPLIN</td>
                                <td>HAMBURG</td>
                                <td><span class="label label-success">DEPARTING</span></td>
                            </tr>

                        </table>


                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-menu-down"></i> &thinsp; Live Arrivals Board</div>
                    <div class="panel-body">

                        <table class="table">

                            <tr>
                                <th>CALLSIGN</th>
                                <th>PILOT</th>
                                <th>ARRIVING FROM</th>
                                <th>STATUS</th>
                            </tr>

                            <tr>
                                <td>BA126</td>
                                <td>SAMUEL JAMES</td>
                                <td>CHAVICANTE</td>
                                <td><span class="label label-success">LANDED</span></td>
                            </tr>

                            <tr>
                                <td>NAX553</td>
                                <td>HARRY SUDGEN</td>
                                <td>OSLO</td>
                                <td><span class="label label-warning">DESCENDING</span></td>
                            </tr>

                            <tr>
                                <td>RYR772</td>
                                <td>LEWIS HARDCASTLE</td>
                                <td>ROME</td>
                                <td><span class="label label-primary">CRUISING</span></td>
                            </tr>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

</body>

{!! HTML::script(elixir("js/app-all.js")) !!}

</html>
