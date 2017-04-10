@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-info-sign"></i> &thinsp; Personal Details</div>
                    <div class="panel-body">
                        <!-- Content Of Panel [START] -->
                        <!-- Top Row [START] -->
                        <div class="row">
                            <div class="col-xs-4">
                                <b>CID:</b>
                                {{ $_account->id }}
                            </div>

                            <div class="col-xs-4">
                                <b>FULL NAME:</b>
                                {{ $_account->name}}
                            </div>

                            @if(false)
                                <div class="col-xs-4">
                                    <b>NICKNAME:</b>
                                    {{ $_account->name }}
                                </div>
                            @endif

                        </div>
                        <!-- Top Row [END] -->

                        <br/>

                        <!-- Second Row [START] -->
                        <div class="row">

                            <div class="col-xs-4">
                                <b>STATUS: </b>
                                {{ $_account->status_string }} {{ !is_null($_account->primary_state) ? $_account->primary_state->name : 'unknown state' }} Member
                            </div>

                            <div class="col-xs-4">
                                <b>LAST SSO LOGIN:</b>

                                @if($_account->last_login_ip)
                                    {{ $_account->last_login_ip }}
                                @else
                                    <em>No login history available.</em>
                                @endif

                            </div>

                            <div class="col-xs-4">
                                <strong>
                                    INVISIBILITY:
                                </strong>

                                @if($_account->is_invisible)
                                    {!! HTML::link("mship/auth/invisibility", "Disable") !!}
                                @else
                                    {!! HTML::link("mship/auth/invisibility", "Enable")  !!}
                                @endif
                            </div>
                        </div>
                        <!-- Second Row [END] -->
                        <!-- Content Of Panel [END] -->

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="glyphicon glyphicon-lock"></i> &thinsp; Secondary Password</div>
                    <div class="panel-body">
                        <!-- Content Of Panel [START] -->
                        <!-- Top Row [START] -->
                        <div class="row">
                            <div class="col-xs-12">
                                Your authentication for VATSIM UK is largely handled by VATSIM.net's certificate server.  From time to time this can go offline,
                                which will prevent you from accessing any UK service.  In order to avoid being impacted by this, members are encouraged to set
                                a secondary password.  In doing so, you will be asked for this password after every login and when the certificate server is offline.
                            </div>
                        </div>
                        <!-- Top Row [END] -->

                        <br/>

                        <!-- Second Row [START] -->
                        <div class="row">

                            <div class="col-xs-4">
                                <b>STATUS: </b>

                                @if($_account->password)
                                    ENABLED
                                @else
                                    DISABLED
                                @endif

                            </div>

                            @if($_account->password)
                                <div class="col-xs-4">
                                    {!! HTML::link("mship/security/replace/0", "Click to Modify") !!}
                                </div>

                                <div class="col-xs-4">
                                        @if(!$_account->mandatory_password)
                                            {!! HTML::link("mship/security/replace/1", "Click to Disable") !!}
                                        @else
                                            Cannot be disabled.
                                        @endif
                                </div>
                            @else
                                <div class="col-xs-4">
                                    {!! HTML::link("mship/security/enable", "Click to Enable") !!}
                                </div>
                            @endif
                        </div>
                        <!-- Second Row [END] -->
                        <!-- Content Of Panel [END] -->

                    </div>
                </div>
            </div>

            @if($_account->hasState("DIVISION") || $_account->hasState("TRANSFERRING"))
                <div class="col-md-12">
                    <div class="panel panel-ukblue">
                        <div class="panel-heading"><i class="fa fa-cogs"></i>
                            &thinsp;
                            Community Groups
                            @if($_account->can('deploy', new \App\Modules\Community\Models\Membership()))
                            <div class="pull-right">
                                    <a href="{{ route("community.membership.deploy") }}">
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                            </div>
                            @endif
                        </div>
                        <div class="panel-body">
                            <div class="row">

                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p align="center">
                                                <b>CURRENT MEMBERSHIP(S)</b>
                                            </p>
                                        </div>

                                        <div class="col-md-12">
                                          <table class="table">
                                                @forelse($_account->communityGroups as $cg)
                                                    <tr>
                                                        <th>{{ $cg->name }}</th>
                                                        <td>{{ HTML::fuzzyDate($cg->pivot->created_at) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <th colspan="2">
                                                            You are not part of any community groups.
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="2">
                                                            {!! link_to_route("community.membership.deploy", "Why not join one now?") !!}
                                                        </th>
                                                    </tr>
                                                @endforelse

                                                @if($_account->communityGroups->count() == 1)
                                                    <tr>
                                                        <th colspan="2">
                                                            You are not part of any <em>region</em>-based groups.
                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <th colspan="2">
                                                            {!! link_to_route("community.membership.deploy", "Why not join one now?") !!}
                                                        </th>
                                                    </tr>
                                                @endif

                                            </table>

                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p align="center">
                                              <b>TOTAL POINTS</b>
                                            </p>
                                        </div>

                                        <div class="col-md-12">
                                            <table class="table">
                                                <tr>
                                                    <th>Weekly</th>
                                                    <td>0</td>
                                                </tr>
                                                <tr>
                                                    <th>Monthly</th>
                                                    <td>0</td>
                                                </tr>
                                                <tr>
                                                    <th>Yearly</th>
                                                    <td>0</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-graduation-cap"></i> &thinsp; ATC & Pilot Qualifications</div>
                    <div class="panel-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6 col-lg-6 col-md-12 row-text-contain text-center">
                                        <b>ATC QUALIFICATIONS</b>
                                        <br />
                                        <small>Showing all achieved</small>
                                    </div>
                                    <div class="col-xs-6 col-lg-6 col-md-12 text-center">
                                        @foreach($_account->qualifications_atc as $qual)
                                            {{ $qual }}
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->pivot->created_at }}">
                                                <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                            </a>
                                            <br />
                                        @endforeach
                                        @if(count($_account->qualifications_atc) < 1)
                                            You have no ATC ratings.
                                        @endif

                                        @foreach($_account->qualifications_atc_training as $qual)
                                            {{ $qual }}
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->pivot->created_at }}">
                                                <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                            </a>
                                            <br />
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-6 col-lg-6 col-md-12 row-text-contain text-center">
                                        <b>PILOT QUALIFICATIONS</b>
                                        <br />
                                        <small>Showing all achieved</small>
                                    </div>
                                    <div class="col-xs-6 col-lg-6 col-md-12 text-center">

                                        @foreach($_account->qualifications_pilot as $qual)
                                            {{ $qual }}
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->pivot->created_at }}">
                                                <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                            </a>
                                            <br />
                                        @endforeach
                                        @if(count($_account->qualifications_pilot) < 1)
                                            You have no Pilot ratings.
                                        @endif

                                        @foreach($_account->qualifications_pilot_training as $qual)
                                            {{ $qual }}
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->pivot->created_at }}">
                                                <em>granted {{ $qual->pivot->created_at }}</em>
                                            </a>
                                            <br />
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="col-md-12">
            <div class="panel panel-ukblue">
                <div class="panel-heading">
                    <i class="fa fa-envelope"></i>&thinsp;
                    Email Addresses
                    <div class="pull-right">
                        <a href="{{ route("mship.manage.email.add") }}">
                            <i class="fa fa-plus-circle"></i>
                        </a>
                        &thinsp;
                        <a href="{{ route("mship.manage.email.assignments") }}">
                            <i class="fa fa-cogs"></i>
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Content Of Panel [START] -->
                    <!-- Top Row [START] -->
                    <div class="row">
                        <div class="col-xs-4">
                            <b>PRIMARY EMAIL:</b>
                            <br />
                            {{ $_account->email }}
                        </div>

                        <div class="col-xs-4">
                            <b>STATUS:</b>
                            <br />
                                Verified
                        </div>
                    </div>
                    <!-- Top Row [END] -->

                    <br/>

                    @forelse($_account->secondaryEmails as $email)
                        <div class="row">
                            <div class="col-xs-4">
                                <b>SECONDARY EMAIL:</b>
                                <br />
                                {{ $email->email }}
                            </div>

                            <div class="col-xs-2">
                                <b>STATUS:</b>
                                <br />
                                @if($email->verified_at == null)
                                    Unverified
                                @else
                                    Verified
                                @endif
                            </div>

                            <div class="col-xs-4 hidden-xs hidden-sm">
                                <b>ADDED:</b>
                                <br />
                                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $email->created_at }}">
                                    <em>on {{ $email->created_at }}</em>
                                </a>
                            </div>
                            <div class="col-xs-2">
                                <a href="{{ route('mship.manage.email.delete', ['email' => $email->id]) }}">
                                    <button type="button" class="btn btn-xs btn-danger">Delete</button>
                                </a>
                            </div>

                        </div>

                        <br />
                    @empty
                        You have no secondary email addresses.
                    @endforelse

                </div>
            </div>
        </div>

            @if(!$_account->is_banned)
                <div class="col-md-12">
                    <div class="panel panel-ukblue">
                        <div class="panel-heading"><i class="glyphicon glyphicon-earphone"></i>
                            &thinsp;
                            TeamSpeak Registrations
                            <div class="pull-right">
                                <a href="{{ route("teamspeak.new") }}">
                                    <i class="fa fa-plus-circle"></i>
                                </a>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-3">
                                    <b>TEAMSPEAK REGISTRATIONS</b>
                                </div>

                                <div class="col-xs-9">
                                    <div class="row">
                                        @if (count($_account->teamspeakRegistrations) == 0)
                                            No registrations found.
                                        @endif
                                        @foreach ($_account->teamspeakRegistrations as $tsreg)
                                            <div class="col-xs-6 row-text-contain">
                                                [ <strong>Registration #{{ $tsreg->id }}</strong> ]<br />
                                                <strong>CREATED</strong>:

                                                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $tsreg->created_at }}">
                                                    <em>{{ $tsreg->created_at->diffForHumans() }}</em>
                                                </a>

                                                <br />
                                                @if (is_null($tsreg->dbid))
                                                    <strong>STATUS</strong>: {!! link_to_route('teamspeak.new', 'New Registration') !!}<br />
                                                @elseif (!is_null($tsreg->dbid))
                                                    <strong>UNIQUE ID</strong>: {{ $tsreg->uid }}<br />
                                                    <strong>LAST IP</strong>: {{ $tsreg->last_ip }}<br />
                                                    <strong>LAST LOGIN</strong>: {{ $tsreg->last_login }}<br />
                                                    <strong>OPERATING SYSTEM</strong>: {{ $tsreg->last_os }}<br />
                                                @endif
                                                [ {!! link_to_route("teamspeak.delete", "Remove Registration", [$tsreg->id]) !!} ]<br />&nbsp;
                                            </div>
                                        @endforeach

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="panel panel-ukblue">
                        <div class="panel-heading"><i class="fa fa-slack"></i>
                            &thinsp;
                            Slack Registration
                            <div class="pull-right">
                                @if(Gate::allows('register-slack'))
                                    <a href="{{ route("slack.new") }}">
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    @if($_account->slack_id)
                                        Currently registered with Slack ID {{ $_account->slack_id }}.
                                    @elseif(Gate::allows('register-slack'))
                                        You are not yet registered.  {!! link_to_route("slack.new", "Click here to register.") !!}
                                    @else
                                        You are not eligible for Slack registration.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
