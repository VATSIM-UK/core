@extends('layout')

@section('content')
    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-plus"></i> &thinsp; Join Us
                </div>
                <div class="panel-body">
                    <p>
                        This page is for new members - <a href="{{ route('visiting.landing') }}">already a member
                            wishing to visit or transfer?</a>
                    </p>

                    <p>
                        Flying and controlling on the VATSIM network is a great way to enjoy the perks of an interactive
                        flight simulation session, and making new friends along the way. To fly online, all you need is
                        a
                        copy of Flight Simulator, Prepar3D or X Plane, and you're ready to go! For controlling, you don't need
                        anything except a working computer, all the software is provided free!
                    </p>

                    <p>
                        This page outlines step-by-step instructions on how to become a pilot or virtual air traffic
                        controller in the UK. Many of our members are both pilots and controllers, so feel free to give
                        them
                        both a try! If you have any problems during the process, please visit our forums or use the
                        "Contact
                        Us" function at the top of the page. If you are already a VATSIM member and want to transfer
                        into
                        the UK for controlling, please see the bottom of this page. Don't forget that VATSIM pilots can
                        fly
                        anywhere they want in the world without having to join our division, but if you primarily fly
                        here
                        in the UK you are encouraged to join our community to get the best experience you can.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-flex">


        <div class="col-md-4 col-md-offset-1">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-headset"></i> &thinsp; Becoming a
                    Controller
                </div>
                <div class="panel-body">
                    <h4>Step 1</h4>
                    <p>Visit <a href="https://my.vatsim.net/register">VATSIM.net</a> and register for your ID and password. This is a very quick process and is
                        completely FREE. Ensure you choose Europe as your region and the UK as your division.</p>

                    <p>If you are already a member of VATSIM, you can view your current region on your <a
                                href="https://my.vatsim.net/profile">VATSIM.net Dashboard</a> to
                        verify
                        that your home region/division is set to 'Europe - United Kingdom'. If not, you will need
                        to
                        make
                        an application to change to the UK in order to become a controller with us. Visit our <a
                                href="https://www.vatsim.uk/community/vt-guide">'Visit
                        or
                        Transfer' page</a> for more information.</p>

                    <h4>Step 2</h4>
                    <p>Download a controller client such as EuroScope. You can use this to connect to VATSIM as an
                        Observer
                        (OBS), to observe proceedings but not yet to control live traffic yourself.</p>

                    <p>
                        Refer to <a href="{{ route('site.atc.newController') }}">Becoming a Controller</a> for
                        information on what to do next to get training for a
                        rating
                        upgrade to Student 1 and begin controlling some traffic!
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-plane"></i> &thinsp; Becoming a Pilot
                </div>
                <div class="panel-body">
                    <h4>Step 1</h4>
                    <p>Visit <a href="https://my.vatsim.net/register">VATSIM.net</a> and register for your ID and password. This is a very quick process and is
                        completely FREE. Feel free to use Europe as your region and the UK as your division,
                        although you
                        can fly wherever you want on VATSIM, regardless of which country you choose to join.</p>

                    <h4>Step 2</h4>
                    <p>Download an <a href="https://my.vatsim.net/learn/vatsim-basics/section/5">approved pilot client</a>. These are the free pieces of software you will use to
                        connect
                        your flight simulator to VATSIM.</p>

                    <h4>Step 3</h4>
                    <p>Visit the <a href="https://vatsim.net/docs/basics/getting-started">Getting Started Guide</a> to get started as a pilot on the
                        network. For
                        flying in the UK, our own <a href="{{ route('site.airports') }}">airport pages</a> contain some great information and
                        resources.</p>

                    <h4>Step 4</h4>
                    <p>Consider reviewing the <a href="https://my.vatsim.net/pilots/train">Pilot Training</a> area of the VATSIM.net Education Hub to enhance your pilot skills or join a
                        distinguished
                        virtual airline like <a href="https://bavirtual.co.uk/">BAVirtual</a>. If you prefer GA flying, you might like to consider
                        visiting the
                        <a href="https://www.cixvfrclub.org.uk/">CIX VFR Club</a>.</p>

                    <h4>Step 5</h4>
                    <p>Get online and fly!</p>
                </div>
            </div>
        </div>
    </div>

@stop