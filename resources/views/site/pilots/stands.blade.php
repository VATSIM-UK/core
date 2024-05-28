@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2 ">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; Stand Allocation in the UK
                </div>
                <div class="panel-body">
                    <p>
                        Stand allocation at VATSIM UK is fully automated via the UK Controller Plugin. The system uses
                        real-world and VATSIM network data to ensure that every flight is assigned the most realistic stand possible. The system
                        is reactive to network events such as pilots connecting on the stand and will re-assign stands accordingly.
                    </p>
                    <p>
                        The ultimate authority for stand assignment rests with the controllers and they have the ability to override the system
                        assignments where required.
                    </p>
                    <p>
                        There are a number of features available to pilots to enhance their experience of arriving into
                        UK airports on the VATSIM network.
                    </p>
                </div>
            </div>
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; Assignment Timings
                </div>
                <div class="panel-body">
                    <p>
                        Stand assignments for arriving aircraft are made approximately <strong>15 minutes prior to arrival.</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; Stand Assignments over ACARS
                </div>
                <div class="panel-body">
                    <p>
                        ACARS stands for <strong>Aircraft Communication Addressing and Reporting System</strong> and is a datalink that transmits
                        messages between aircraft and ground stations. The system is used by airlines to relay operational information, and is used
                        in some countries as an alternative to voice communication for ATC clearances.
                    </p>
                    <p>
                        VATSIM UK provides a stand assignment over ACARS service to pilots. When an arrival stand assignment is made,
                        the system will send an ACARS message over <strong>Hoppie's ACARS Network</strong> if the user is logged in. This will be received 
                        and displayed by the pilots ACARS client. This system can be helpful in preparing to arrive at a busy airport, or to enhance
                        the realism of your arrival.
                    </p>
                    <p>
                        The system configurable for each user - you can opt to always receive your stand assignment via ACARS
                        (the system always assigns you a stand, even if there's no controllers online!), or you can opt to only receive it when
                        there's someone controlling at your destination airfield.
                    </p>
                    <p>
                        To enable this feature, please visit
                        the <strong><a href="https://ukcp.vatsim.uk/admin/my-preferences">UK Controller Plugin preferences page</a></strong> and select your preffered options.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="glyphicon glyphicon-plane"></i> &thinsp; Request a Stand
                </div>
                <div class="panel-body">
                    <p>
                        VATSIM UK also offers pilots the ability to request a stand for arrival. This allows pilots to indicate
                        their preferences to the stand allocator, which will then attempt to assign them that stand for arrival.
                    </p>
                    <p>
                        To request a stand, make sure that you're logged into the VATSIM network and have filed your flightplan.
                        Once you've done this, please fill in the <strong><a href="https://ukcp.vatsim.uk/request-a-stand">stand request form</a></strong>.
                    </p>
                    <p>
                        Please note that stand requests <strong>are not a reservation</strong> and do not give you exclusive use of a stand.
                        Other members are welcome to request the same stand and may also choose to connect onto it. Stand requests are honoured
                        on a "first arrival first served" basis.
                    </p>
                    <p>
                        When allocating stands to other aircraft, the stand allocator will always try to assign the most
                        realistic stand for that flight, even if there is an active request. However, where multiple options
                        exist, then it will favour non-requested stands.
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
