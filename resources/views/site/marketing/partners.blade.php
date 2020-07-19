@extends('layout')

@section('content')
    <div class="row">
        <!-- starts corporate partners panel -->
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading">Corporate Partners
                </div>
                <div class="panel-body text-center font-italic">
                    There are currently no corporate partners to display.
                </div>
            </div>
        </div>
        <!-- ends corporate partners panel -->

        <!-- starts community partners panel -->
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading">Community Partners
                </div>
                <div class="panel-body">
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <img class="img-responsive align-middle" src="/images/partners/vraf.png">
                        </div>
                        <div class="col-md-8">
                            <p>Virtual Royal Air Force (vRAF)</p>
                            <p><small>vRAF simulates the Royal Air Force's daily activities taking part in a variety of training and operational exercises including; air-to-air refueling, carrier operations, low-level flying and more.</small></p>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <img class="img-responsive align-middle" src="/images/partners/bavirtual.png">
                        </div>
                        <div class="col-md-8">
                            <p>BAVirtual</p>
                            <p><small>Established in 2000, BAVirtual is Virtual Airline that fulfills a role to provide a structured, hands-on educational environment for aspiring pilots that accurately mimic the operations of British Airways.</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ends community partners panel -->
    </div>
@stop
