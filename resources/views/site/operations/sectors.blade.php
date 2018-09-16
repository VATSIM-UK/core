@extends('layout')

@section('content')

    <div class="row">

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-map-marker"></i> &thinsp; Area Sectors
                </div>
                <div class="panel-body">
                    <p>
                        The London (EGTT) and Scottish (EGPX) Flight Information Regions (FIRs) cover the enroute
                        airspace
                        of the United Kingdom and coverage for underlying approach or aerodrome positions that are not
                        online (&quot;top-down&quot;). The London airspace is split into sectors that may seem daunting
                        at
                        first, but are easy to understand if you take a few minutes to digest the information below. The
                        main London sectors (North, South, Central &amp; West) are shown in the diagram below. These
                        sectors
                        are able to be split further during high traffic situations.
                    </p>

                    <img class="img-responsive center-block" src="/images/areasector1.png">

                    <p>
                        The diagram below illustrates the most basic of splits for Scottish (SCO_CTR) airspace. SCO_CTR
                        covers both areas in the absence of a split controller.
                    </p>

                    <p>
                        <img class="img-responsive center-block" src="/images/areasector2.png">
                    </p>

                    <p>
                        If there are no aerodrome or approach services at your departure aerodrome, use this list to
                        determine which area sector to call. Contact the first controller listed that is online.&nbsp;If
                        in
                        doubt, don&#39;t be afraid to ask.&nbsp;Another good place to check is the controller
                        information,
                        where information is usually displayed as to which areas the controller is covering.
                    </p>
                    <p>
                        If there are no aerodrome or approach services at your departure aerodrome, use this list to
                        determine which area sector to call. Contact the first controller listed that is online.&nbsp;If
                        in doubt, don&#39;t be afraid to ask.&nbsp;Another good place to check is the controller
                        information, where information is usually displayed as to which areas the controller is
                        covering.
                    </p>

                    <p>
                        <strong>EGCC / EGNJ / EGCN / EGNT / EGNV / EGNM</strong><br>
                        MAN_E, MAN, LON_N, LON
                    </p>

                    <p>
                        <strong>EGGP / EGNR / EGNS / EGNH</strong><br>
                        MAN_W, MAN, LON_N, LON
                    </p>

                    <p>
                        <strong>EGNX / EGBB / EGBE</strong><br>
                        LON_C, LON
                    </p>

                    <p>
                        <strong>EGSS / EGGW</strong><br>
                        LTC_NW, LTC_N, LON_C, LON
                    </p>

                    <p>
                        <strong>EGLL / EGLC / EGKB</strong><br>
                        LTC_SE, LTC_S, LON_D, LON_S, LON
                    </p>

                    <p>
                        <strong>EGKK</strong><br>
                        LTC_SW, LTC_S, LON_S, LON
                    </p>

                    <p>
                        <strong>EGHI / EGHH</strong><br>
                        LON_S, LON
                    </p>

                    <p>
                        <strong>EGGD / EGFF / EGTE / EGBJ / EGHQ / EGJJ / EGJB / EGJA</strong><br>
                        LON_W, LON
                    </p>

                    <p>
                        <strong>EGPH / EGPF / EGPK</strong><br>
                        STC, SCO_D, SCO_WD, SCO
                    </p>

                    <p>
                        <strong>EGAA / EGAC</strong><br>
                        STC_A, SCO_R, SCO_W, SCO_WD, SCO
                    </p>

                    <p>
                        <strong>EGPD / EGPE</strong><br>
                        SCO_E, SCO
                    </p>

                    <p>
                        <strong>Frequencies</strong>
                    </p>

                    <p>
                        Standard Frequencies - London
                    </p>

                    <p>
                        This table highlights the frequencies you may see on a typical night, without splitting from the
                        primary sectors.
                    </p>

                    <p>
                        <strong>Sector Splits - London</strong>
                    </p>

                    <p>
                        For the cases where the 4 main sectors are split (common for events), additional frequencies
                        (plus the coverage order) is displayed below.
                    </p>

                    <p>
                        <strong>Radio Callsigns</strong>
                    </p>

                    <p>
                        All area control sectors within the UK (listed above) are called either &quot;London Control&quot;
                        or &quot;Scottish Control&quot; over RT. London Area (LON) and London Terminal (LTC) Control are
                        referred to as &quot;London Control&quot;, regardless of which sector you are speaking to.
                        Equally Scottish Area (SCO) and Scottish Terminal (STC) Control are referred to as &quot;Scottish
                        Control&quot; regardless of sector.
                    </p>

                    <p>
                        The Manchester TMA sectors (MAN) are splits of the London North sector, however are referred to
                        as &quot;Scottish Control&quot; when open. When only the parent sector, London North (LON_N), is
                        online they are still referred to as &quot;London Control&quot; regardless of which part of the
                        sector you are in.
                    </p>

                    <p>
                        <strong>Primary Sectors</strong>
                    </p>

                    <p>
                        For the purpose of controller bookings, the &#39;Primary Sectors&#39; are defined as:
                    </p>

                    <ul>
                        <li>
                            LON_S_CTR
                        </li>
                        <li>
                            LON_C_CTR
                        </li>
                        <li>
                            LON_W_CTR
                        </li>
                        <li>
                            LON_N_CTR
                        </li>
                        <li>
                            SCO_CTR
                        </li>
                    </ul>

                </div>
            </div>
        </div>

    </div>

@stop
