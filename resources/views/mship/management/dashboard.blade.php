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
                                {{ $_account->status_string }} {{ $_account->current_state }}
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

                                @if($_account->current_security)
                                    ENABLED
                                @else
                                    DISABLED
                                @endif

                            </div>

                            @if($_account->current_security)
                                <div class="col-xs-4">
                                    {!! HTML::link("mship/security/replace/0", "Click here to modify.") !!}
                                </div>

                                <div class="col-xs-4">
                                        @if($_account->current_security->security->optional)
                                            {!! HTML::link("mship/security/replace/1", "Click here to disable.") !!}
                                        @else
                                            You are not permitted to disable this.
                                        @endif
                                </div>
                            @endif
                        </div>
                        <!-- Second Row [END] -->
                        <!-- Content Of Panel [END] -->

                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><i class="fa fa-graduation-cap"></i> &thinsp; ATC & Pilot Qualifications</div>
                    <div class="panel-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-4">
                                        <b>ATC QUALIFICATIONS</b>
                                        <br />
                                        <small>Showing all achieved</small>
                                    </div>

                                    <div class="col-xs-8">

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
                                                <em>granted {{ $qual->pivot->created_at }}</em>
                                            </a>
                                            <br />
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-xs-4">
                                        <b>PILOT QUALIFICATIONS</b>
                                        <br />
                                        <small>Showing all achieved</small>
                                    </div>

                                    <div class="col-xs-8">

                                        @foreach($_account->qualifications_pilot as $qual)
                                            {{ $qual }}
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                                                <em>granted {{ $qual->created_at->diffForHumans() }}</em>
                                            </a>
                                            <br />
                                        @endforeach
                                        @if(count($_account->qualifications_pilot) < 1)
                                            You have no ATC ratings.
                                        @endif

                                        @foreach($_account->qualifications_pilot_training as $qual)
                                            {{ $qual }}
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                                                <em>granted {{ $qual->created_at }}</em>
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
                                Verified
                        </div>
                    </div>
                    <!-- Top Row [END] -->

                    <br/>

                    @foreach($_account->secondaryEmails as $email)
                        <div class="row">
                            <div class="col-xs-4">
                                <b>SECONDARY EMAIL:</b>
                                <br />
                                {{ $email->email }}
                            </div>

                            <div class="col-xs-4">
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
                                    <em>added {{ $email->created_at }}</em>
                                </a>
                            </div>

                        </div>

                        <br />
                    @endforeach
                    @if(count($_account->emails) < 2)
                        You have no secondary email addresses.
                    @endif

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
                                            <div class="col-xs-6">
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
                                @if($_account->hasState(\App\Models\Mship\Account\State::STATE_DIVISION))
                                    <a href="{{ route("slack.new") }}">
                                        <i class="fa fa-plus-circle"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    @if($_account->hasState(\App\Models\Mship\Account\State::STATE_DIVISION))
                                        @if($_account->slack_id)
                                            Current registered with Slack ID {{ $_account->slack_id }}.
                                        @else
                                            You are not yet registered.  {!! link_to_route("slack.new", "Click here to register.") !!}
                                        @endif
                                    @else
                                        You are not elegible for Slack registration as you are not a UK member.
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
