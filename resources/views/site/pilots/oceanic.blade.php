@extends('layout')

@section('content')

    <div class="row">

        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-globe"></i> &thinsp; Oceanic Procedures
                </div>
                <div class="panel-body">
                    <p>
                        Shanwick and Gander control area (OCA) is an ATC environment with no surveillance radar
                        capability. No surveillance radar capability means that the ATC cannot use radar to control
                        aircraft. It is a non-radar environment. Therefore, the Oceanic controller receives estimates
                        and position reports in order&nbsp;to provide procedural separation while flying in Oceanic FIR.
                        In real life, Controller-pilot data link communications (CPDLC) and Aircraft Communications
                        Addressing and Reporting System (ACARS) data-link communications is sent and received directly
                        between controllers and pilots. And high frequency (HF) radio remains the primary means of voice
                        communications.
                    </p>

                    <p>
                        On the VATSIM network, HF and ACARS is, per today, unfortunately not practicable. However, CPDLC
                        and voice communication via Very High Frequency (VHF) is available. Not all controllers are
                        equipped with CPDLC software, and as it is not a controller requirement, this is not a
                        requirement for pilots to have in order to fly over the North Atlantic Ocean.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <iframe width="100%" height="500px"
                    src="https://www.youtube-nocookie.com/embed/6pI77r3oAxw?rel=0&amp;controls=0&amp;showinfo=0&amp;start=3"
                    frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>

        </div>

    </div>

@stop
