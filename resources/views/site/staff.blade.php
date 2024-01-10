@extends('layout')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Staff
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
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Contact Us
                </div>
                <div class="panel-body text-center">
                    <p>Need to talk to a member of staff? All our staff members can be contacted through our <strong>helpdesk</strong></p>
                    <a href="https://helpdesk.vatsim.uk" target="_blank" class="btn btn-info">Contact Staff / Department <i class="fa fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Management
                </div>
                <div class="panel-body">

                    <div class="col-md-12">
                        <h4 class="text-center">Division Director (VATUK1)</h4><br />
                        <img src="{{ $teamPhotos[7103] }}" width=50px class="img-responsive center-block profile-picture" />
                        <p class="text-center">Ben Wright</p>
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
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Operations
                </div>
                <div class="panel-body">
                    <h4 class="text-center">Operations Director (VATUK9)</h4><br />
                    <img src="{{ $teamPhotos[4078] }}" width=50px class="img-responsive center-block profile-picture" />
                    <p class="text-center">Kieran Hardern</p>
                    <h4 class="text-center">Operations Team</h4>
                    <table class="table">
                        <tr>
                            <td>Enroute Operations Coordinator</td>
                            <td><em>Vacant</em></td>
                        </tr>
                        <tr>
                            <td>Aerodrome Operations Coordinator</td>
                            <td>Adam Arkley</td>
                        </tr>
                        <tr>
                            <td>Aerodrome Operations Coordinator</td>
                            <td>Kye Taylor</td>
                        </tr>
                        <tr>
                            <td>Sector File Coordinator</td>
                            <td>Luke Brown</td>
                        </tr>
                        <tr>
                            <td>Sector File Coordinator</td>
                            <td>Peter Mooney</td>
                        </tr>
                        <tr>
                            <td>Operations Assistant</td>
                            <td>Chad Byworth</td>
                        </tr>
                        <tr>
                            <td>Operations Assistant</td>
                            <td>1237658</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Operations -->
        <!-- Marketing -->
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Marketing
                </div>
                <div class="panel-body">
                    <h4 class="text-center">Marketing Director (VATUK4)</h4><br />
                    <img src="{{ $teamPhotos[1] }}" width=50px class="img-responsive center-block profile-picture" />
                    <p class="text-center"><em>Vacant</em></p>
                    <h4 class="text-center">Marketing Team</h4>
                    <table class="table">
                        <tr>
                            <td>Marketing General Manager</td>
                            <td><em>Vacant</em></td>
                        </tr>
                        <tr>
                            <td>Events Manager</td>
                            <td>Luke Thompson</td>
                        </tr>
                        <tr>
                            <td>Media Manager</td>
                            <td>Thomas Hallam</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Marketing -->
        <!-- Web Services -->
        <div class="col-md-4">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Web Services
                </div>
                <div class="panel-body">
                    <h4 class="text-center">Web Services Director (VATUK8)</h4><br />
                    <img src="{{ $teamPhotos[5125] }}" class="img-responsive center-block profile-picture" />
                    <p class="text-center">Calum Tοwers</p>

                    <h4 class="text-center">Web Services Team</h4>
                    <table class="table">
                        <tr>
                            <td>Web Services Manager<br />
                                Developer
                            </td>
                            <td>Callum Axon</td>
                        </tr>
                        <tr>
                            <td>Developer</td>
                            <td>Alex Toff</td>
                        </tr>
                        <tr>
                            <td>Developer</td>
                            <td>Andy Ford</td>
                        </tr>
                        <tr>
                            <td>Developer</td>
                            <td>Ben Esen</td>
                        </tr>
                        <tr>
                            <td>Developer</td>
                            <td>Dave Etheridge</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Web Services -->
    </div>
    <!-- End Row One -->
</div>
<div class="container">
    <!-- Row Two -->
    <div class="row">
        <!-- 1 Column for Two -->
        <div class="col-md-4">
            <!-- Member Services -->
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Member Services
                </div>
                <div class="panel-body">
                    <h4 class="text-center">Member Services Director (VATUK3)</h4><br />
                    <img src="{{ $teamPhotos[5660] }}" width=50px class="img-responsive center-block profile-picture" />
                    <p class="text-center">John Batten</p>
                    <h4 class="text-center">Member Services Team</h4>
                    <table class="table">
                        <tr>
                            <td>Member Services Assistant</td>
                            <td>James Thomas</td>
                        </tr>
                        <tr>
                            <td>Member Services Assistant</td>
                            <td>William Brushfield</td>
                        </tr>
						<tr>
                            <td>Member Services Assistant</td>
                            <td>William Shaw</td>
                        </tr>
                    </table>
                </div>
            </div>
            <!-- Other Roles -->
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Other Roles
                </div>
                <div class="panel-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Data Protection Officer</td>
                                <td>Chris Pawley</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- End Other Roles -->
        </div>
        <!-- End 1 Column for Two -->
        <!-- Training -->
        <div class="col-md-8">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-star"></i> &thinsp; Training
                </div>
                <div class="panel-body">
                    <div class="col-md-6">
                        <h4 class="text-center">ATC Training Director (VATUK5)</h4><br />
                        <img src="{{ $teamPhotos[2308] }}" width=50px class="img-responsive center-block profile-picture" />
                        <p class="text-center">Adam Arkley</p>
                        <h4 class="text-center">ATC Training Team</h4>
                        <table class="table">
                            <tr>
                                <td>ATC Training Manager</td>
                                <td>Will Jennings</td>
                            </tr>
                            <tr>
                                <td>Training Department Assistant</td>
                                <td>Craig Stewart</td>
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
                                <td>Fergus Walsh</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (New Controller)</td>
                                <td>James Taylor</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (New Controller)</td>
                                <td>John Batten</td>
                            </tr>
                            <tr>
                                <td>Lead Mentor (New Controller)</td>
                                <td>Stephen Lee</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (TWR)</td>
                                <td>Daniel Blanco</td>
                            </tr>
                            <tr>
                                <td>Lead Mentor (TWR)</td>
                                <td>Will Hinshaw</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (APP)</td>
                                <td>Samuel Lefevre</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (ENR)</td>
                                <td><em>Vacant</em></td>
                            </tr>
                            <tr>
                                <td>TG Instructor (Heathrow)</td>
                                <td>Fraser Cooper</td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h4 class="text-center">Pilot Training Director (VATUK6)</h4><br />
                        <img src="{{ $teamPhotos[7203] }}" width=50px class="img-responsive center-block profile-picture" />
                        <p class="text-center">Darren Hill</p>
                        <h4 class="text-center">Pilot Training Team</h4>
                        <table class="table">
                            <tr>
                                <td>Pilot Training Manager</td>
                                <td><em>Vacant</em></td>
                            </tr>
                            <tr>
                                <td>Initial Flight Instructor</td>
                                <td>Cole Edwards</td>
                            </tr>
                            <tr>
                                <td>VFR Flight Instructor</td>
                                <td>Benjamin Arrowsmith</td>
                            </tr>
                            <tr>
                                <td>IFR Flight Instructor</td>
                                <td>Firas Bashee</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Training -->
    </div>
</div>
<!-- End Row Two -->
</div>
@stop
