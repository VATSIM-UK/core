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
        <div class="row">
            <div class="col-md-8">
                <!-- Row One -->
                <div class="row row-eq-height">
                    <div class="col-md-6">
                        <div class="panel panel-ukblue">
                            <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Community
                            </div>
                            <div class="panel-body">
                                <h4>Community Director (VATUK3)</h4><br/>
                                <img src="{{ $teamPhotos[2311] }}"
                                     width=50px
                                     class="img-responsive center-block profile-picture"/>
                                <p class="text-center">Barrie Joplin</p>

                                <h4>Community Team</h4>
                                <table class="table">
                                    <tr>
                                        <td>Community Manager</td>
                                        <td>Nick Marinov</td>
                                    </tr>
                                    <tr>
                                        <td>Community Manager</td>
                                        <td>Leon Grant</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel panel-ukblue">
                            <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Marketing
                            </div>
                            <div class="panel-body">
                                <h4>Marketing Director (VATUK4)</h4><br/>
                                <img src="{{ $teamPhotos[5161] }}"
                                     width=50px
                                     class="img-responsive center-block profile-picture"/>
                                <p class="text-center">Trevor Hannant</p>

                                <h4>Marketing Team</h4>
                                <table class="table">
                                    <tr>
                                        <td>Social Media Manager</td>
                                        <td>Loui Ringer</td>
                                    </tr>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row One -->

                <!-- Row Two -->
                <div class="row row-eq-height">
                    <div class="col-md-6">
                        <div class="panel panel-ukblue">
                            <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Pilot Training
                            </div>
                            <div class="panel-body">
                                <h4>Pilot Training Director (VATUK6)</h4><br/>
                                <img src="{{ $teamPhotos[6286] }}"
                                     class="img-responsive center-block profile-picture"/>
                                <p class="text-center">Daniel Crookes</p>

                                <h4>Pilot Training Team</h4>
                                <table class="table">
                                    <tr>
                                        <td>Administrative Manager</td>
                                        <td>Matthew Wilson</td>
                                    </tr>
                                    <tr>
                                        <td>Initial Flight Instructor</td>
                                        <td><em>Vacant</em></td>
                                    </tr>
                                    <tr>
                                        <td>VFR Flight Instructor</td>
                                        <td>Lewis Hammett</td>
                                    </tr>
                                    <tr>
                                        <td>IFR Flight Instructor</td>
                                        <td>Tom Knowles</td>
                                    </tr>
                                    <tr>
                                        <td>Development Flight Instructor</td>
                                        <td>James Gibson</td>
                                    </tr>
                                </table>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="panel panel-ukblue">
                            <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; Web Services
                            </div>
                            <div class="panel-body">
                                <h4>Web Services Director (VATUK8)</h4><br/>
                                <img src="{{ $teamPhotos[5125] }}" width=50px
                                     class="img-responsive center-block profile-picture"/>
                                <p class="text-center">Calum Tοwers</p>

                                <h4>Web Services Team</h4>
                                <table class="table">
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
                                        <td>Callum Axon</td>
                                    </tr>
                                    <tr>
                                        <td>Junior Developer</td>
                                        <td>Matt Collier</td>
                                    </tr>
                                    <tr>
                                        <td>Support</td>
                                        <td>George Barlow</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Row Two -->

                <!-- Row Three -->
                <div class="row row-eq-height">
                    <div class="col-md-6">
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
                </div>
                <!-- End Row Three -->
            </div>


            <div class="col-md-4">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-star"></i> &thinsp; ATC Training
                    </div>
                    <div class="panel-body">
                        <h4>ATC Training Director (VATUK5)</h4><br/>
                        <img src="{{ $teamPhotos[4366] }}"
                             width=50px
                             class="img-responsive center-block profile-picture"/>
                        <p class="text-center">Andy Ford</p>

                        <h4>ATC Training Team</h4>
                        <table class="table">
                            <tr>
                                <td>Administrative Manager</td>
                                <td>Alex Beard</td>
                            </tr>
                            <tr>
                                <td>Senior Division Instructor<br/>
                                    TG Instructor (CTR)
                                </td>
                                <td>Phillip Speer</td>
                            </tr>
                            <tr>
                                <td>Senior Division Instructor<br/>
                                    Head Examiner
                                </td>
                                <td>George Wright</td>
                            </tr>
                            <tr>
                                <td>Senior Division Instructor</td>
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
                                <td>TG Manager (TGNC)<br/>
                                    TG Manager (Heathrow)
                                </td>
                                <td>Oliver Rhodes</td>
                            </tr>
                            <tr>
                                <td>TG Manager (TGNC)</td>
                                <td>Josh Howker</td>
                            </tr>
                            <tr>
                                <td>TG Manager (TGNC)</td>
                                <td>Tom Szczypinski</td>
                            </tr>
                            <tr>
                                <td>TG Manager (TWR)</td>
                                <td>Lee Roberts</td>
                            </tr>
                            <tr>
                                <td>TG Manager (APP)</td>
                                <td>Nick Marinov</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (TG1)</td>
                                <td>Jamie Paine</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (TG1)</td>
                                <td>Nathan Donnelly</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (TG2)</td>
                                <td>Jonas Hey</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (TG2)</td>
                                <td>Luke Collister</td>
                            </tr>
                            <tr>
                                <td>TG Instructor (Heathrow)</td>
                                <td>James Yuen</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop