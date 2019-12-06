@extends('layout')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Staff
                    </div>
                    <div class="panel-body">
                        <p>VATSIM UK is led by the Division Director and is managed at a strategic level by the Division
                            Staff Group comprised of the heads of each department. Department staff may be appointed
                            that
                            report to the relevant member of the DSG.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Contact Us
                    </div>
                    <div class="panel-body text-center">
                        <p>Need to talk to a member of staff? All our staff members can be contacted through our <strong>helpdesk</strong></p>
                        <a href="https://helpdesk.vatsim.uk" target="_blank" class="btn btn-info">Contact Staff / Department <i class="glyphicon glyphicon-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Management
                    </div>
                    <div class="panel-body">

                        <div class="col-md-6">
                            <h4 class="text-center">Division Director (VATUK1)</h4><br/>
                            <img src="{{ $teamPhotos[91] }}"
                                 width=50px
                                 class="img-responsive center-block profile-picture"/>
                            <p class="text-center">Simon Irvine</p>
                        </div>

                        <div class="col-md-6">
                            <h4 class="text-center">Deputy Division Director (VATUK2)</h4><br/>
                            <img src="{{ $teamPhotos[3580] }}"
                                 width=50px
                                 class="img-responsive center-block profile-picture"/>
                            <p class="text-center">Nathan Donnelly</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Row One -->
        <div class="row row-eq-height">
            <!-- Operations -->
            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Operations
                    </div>
                    <div class="panel-body">
                        <h4>Operations Director (VATUK9)</h4><br/>
                        <img src="{{ $teamPhotos[54] }}"
                             width=50px
                             class="img-responsive center-block profile-picture"/>
                        <p class="text-center">Chris Pawley</p>
                    </div>
                </div>
            </div>

            <!-- Marketing -->
            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Marketing
                    </div>
                    <div class="panel-body">
                        <h4>Marketing Director (VATUK4)</h4><br/>
                        <img src="{{ $teamPhotos[6738] }}"
                             width=50px
                             class="img-responsive center-block profile-picture"/>
                        <p class="text-center">Loui Ringer</p>

                        <h4>Marketing Team</h4>
                        <table class="table">
                            <tr>
                                <td>Marketing Manager</td>
                                <td>Ben Wright</td>
                            </tr>
                            <tr>
                                <td>Marketing Manager</td>
                                <td>Harrison Grose</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Web Services -->
            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Web Services
                    </div>
                    <div class="panel-body">
                        <h4>Web Services Director (VATUK8)</h4><br/>
                        <img src="{{ $teamPhotos[5125] }}"
                             class="img-responsive center-block profile-picture"/>
                        <p class="text-center">Calum Tοwers</p>

                        <h4>Web Services Team</h4>
                        <table class="table">
                            <tr>
                                <td>Web Services Manager<br/>
                                    Developer
                                </td>
                                <td>Callum Axon</td>
                            </tr>
                            <tr>
                                <td>System Administrator</td>
                                <td>Nathan Davies</td>
                            </tr>
                            <tr>
                                <td>Developer</td>
                                <td>Alex Toff</td>
                            </tr>
                            <tr>
                                <td>Developer</td>
                                <td>Andy Ford</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>


        </div>
        <!-- End Row One -->
    </div>
    <div class="container">
                <!-- Row Two -->
        <div class="row">
            <!-- Training -->
            <div class="col-md-8">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Training
                    </div>
                    <div class="panel-body">
                        <h4>Training Director (VATUK5)</h4><br/>
                        <img src="{{ $teamPhotos[6286] }}"
                             width=50px
                             class="img-responsive center-block profile-picture"/>
                        <p class="text-center">Daniel Crookes</p>

                        <div class="col-md-6">
                           <h4>ATC Training Team</h4>
                            <table class="table">
                                <tr>
                                    <td>General Manager</td>
                                    <td>Oliver Rhodes</td>
                                </tr>
                                <tr>
                                    <td>Division Instructor</td>
                                    <td>Phillip Speer</td>
                                </tr>
                                <tr>
                                    <td>Division Instructor</td>
                                    <td>Chris Pawley</td>
                                </tr>
                                <tr>
                                    <td>Division Instructor</td>
                                    <td>Henry Cleaver</td>
                                </tr>
                                <tr>
                                    <td>Division Instructor</td>
                                    <td>Mike Pike</td>
                                </tr>
                                <tr>
                                    <td>Division Instructor</td>
                                    <td>Jamie Paine</td>
                                </tr>
                                <tr>
                                    <td>TG Manager (TWR)</td>
                                    <td>Adam Meade</td>
                                </tr>
                                <tr>
                                    <td>TG Manager (APP)</td>
                                    <td>Nick Marinov</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (New Controller)</td>
                                    <td>Ben Cook</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (New Controller)</td>
                                    <td>Fergus Walsh</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (TG1)</td>
                                    <td>Nathan Donnelly</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (TG1)</td>
                                    <td>Oliver Gates</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (TG2)</td>
                                    <td>Lee Roberts</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (TG2)</td>
                                    <td>George Peppard</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (Heathrow)</td>
                                    <td>Nathaniel Leff</td>
                                </tr>
                                <tr>
                                    <td>TG Instructor (Enroute)</td>
                                    <td>Sebastian Rekdal</td>
                                </tr>
                            </table>
                            </div>

                        <div class="col-md-6">
                            <h4>Pilot Training Team</h4>
                            <table class="table">
                                <tr>
                                    <td>General Manager</td>
                                    <td>Tom Sirett</td>
                                </tr>
                                <tr>
                                    <td>Administrative Manager<br/>
                                        Initial Flight Instructor
                                    </td>
                                    <td>Matthew Wilson</td>
                                </tr>
                                <tr>
                                    <td>VFR Flight Instructor</td>
                                    <td>Vacant</td>
                                </tr>
                                <tr>
                                    <td>IFR Flight Instructor</td>
                                    <td>Freddie Charlesworth</td>
                                </tr>
                                <tr>
                                    <td>Development Flight Instructor</td>
                                    <td>Vacant</td>
                                </tr>
                             </table>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Roles -->
            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Other Roles
                    </div>
                    <div class="panel-body">
                        <br/>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Division Conflict Resolution Manager</td>
                                    <td>Sean Readman</td>
                                </tr>
                                <tr>
                                    <td>Data Protection Officer</td>
                                    <td>Chris Pawley</td>
                                </tr>
                            </tbody
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- End Row Two -->
    </div>
@stop
