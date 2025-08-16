@extends('layout')

@section('content')
    <div class="modal fade" id="primaryEmailChangeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Change your primary VATSIM email address</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <p>
                            <strong>All primary email changes are handled by central membership, and may take up-to 24
                                hours to be reflected on our systems.</strong>
                        </p>
                    </div>
                    <div class="panel panel-ukblue border-black-thin">
                        <div class="panel-heading">
                            <i>https://my.vatsim.net/user/email</i>
                        </div>
                        <div class="panel-body">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item"
                                        src="https://my.vatsim.net/user/email"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-male"></i> &thinsp; Personal Details

                    <div class="pull-right">
                        <a
                        class="tooltip_displays"
                        href="{{ route('mship.manage.cert.update') }}"
                        data-toggle="tooltip"
                        title="{{ !is_null($_account->cert_checked_at) ? 'Last updated with VATSIM.net ' . $_account->cert_checked_at->diffForHumans() : 'Not yet updated with VATSIM.net.' }} "
                        >
                            <i class="fa fa-sync"></i>
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <!-- Content Of Panel [START] -->
                    <!-- Top Row [START] -->
                    <div class="row">
                        <div class="col-xs-4 pb-1">
                            <b>CID:</b>
                            {{ $_account->id }}
                        </div>
                        <div class="col-xs-4 pb-1">
                            <b>FULL NAME:</b>
                            {{ $_account->name}}
                        </div>
                        @if(false)
                            <div class="col-xs-4 pb-1">
                                <b>NICKNAME:</b>
                                {{ $_account->name }}
                            </div>
                        @endif
                        <div class="col-xs-4 pb-1">
                            <b>MEMBERSHIP:</b>
                            {{ $_account->status_string }} {{ !is_null($_account->primary_state) ? $_account->primary_state->name : 'unknown state' }}
                            Member
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
                            <form action="{{ route('mship.auth.invisibility') }}" id="invisibility-form" method="POST">
                                @csrf
                            <strong>FORUM INVISIBILITY:</strong>
                            <a href="{{ route('mship.auth.invisibility') }}"
                               onclick="event.preventDefault(); document.getElementById('invisibility-form').submit();">{{ $_account->is_invisible ? 'Disable' : 'Enable' }}</a>
                            </form>
                        </div>

                        <div class="col-xs-4">
                            <strong>CONTROLLER ROSTER:</strong>
                            @if($roster)
                                <a href="{{ route('site.roster.show', ['account' => $_account->id]) }}">
                                    Active
                                </a>
                            @else
                                <a href="{{ route('site.roster.index') }}">
                                    Inactive
                                </a>
                            @endif
                        </div>
                    </div>
                    <!-- Top Row [END] -->
                </div>
                <div class="panel-footer panel-footer-primary">
                    <div class="row">
                        <div class="col-xs-12">
                            <a href="{{ route('mship.manage.cert.update') }}">
                                <span class='fa fa-sync'></span> Details look incorrect? Click here to request an update from VATSIM.net.
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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
                            <br/>
                            {{ $_account->email }}
                        </div>

                        <div class="col-xs-2">
                            <b>STATUS:</b>
                            <br/>
                            Verified
                        </div>
                        <div class="col-xs-4 hidden-xs hidden-sm">
                            <br/>
                        </div>
                        <div class="col-xs-2">
                            <br>
                            <button type="button" class="btn btn-xs btn-warning" data-toggle="modal"
                                    data-target="#primaryEmailChangeModal">
                                Change
                            </button>
                        </div>
                    </div>
                    <!-- Top Row [END] -->
                    <br/>
                    @forelse($_account->secondaryEmails as $email)
                        <div class="row">
                            <div class="col-xs-4">
                                <b>SECONDARY EMAIL:</b>
                                <br/>
                                {{ $email->email }}
                            </div>
                            <div class="col-xs-2">
                                <b>STATUS:</b>
                                <br/>
                                @if($email->verified_at == null)
                                    Unverified
                                @else
                                    Verified
                                @endif
                            </div>
                            <div class="col-xs-4 hidden-xs hidden-sm">
                                <b>ADDED:</b>
                                <br/>
                                <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                   title="{{ $email->created_at }}">
                                    <em>on {{ $email->created_at }}</em>
                                </a>
                            </div>
                            <div class="col-xs-2">
                                <br>
                                <a href="{{ route('mship.manage.email.delete', ['email' => $email->id]) }}"
                                   class="btn btn-xs btn-danger">
                                    Delete
                                </a>
                            </div>
                        </div>
                        <br/>
                    @empty
                        You have no secondary email addresses.
                    @endforelse
                </div>
            </div>
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-lock"></i> &thinsp; Secondary Password
                </div>
                <div class="panel-body">
                    <!-- Content Of Panel [START] -->
                    <!-- Top Row [START] -->
                    <div class="row">
                        <div class="col-xs-12">
                            Your authentication for VATSIM UK is largely handled by VATSIM.net's certificate server.
                            From time to time this can go offline,
                            which will prevent you from accessing any UK service. In order to avoid being impacted
                            by this, members are encouraged to set
                            a secondary password. In doing so, you will be asked for this password after every login
                            and when the certificate server is offline.
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
                                <a href="{{ route('password.change') }}">Click to Modify</a>
                            </div>
                            <div class="col-xs-4">
                                @if(!$_account->mandatory_password)
                                    <a href="{{ route('password.delete') }}">Click to Disable</a>
                                @else
                                    Cannot be disabled.
                                @endif
                            </div>
                        @else
                            <div class="col-xs-4">
                                <a href="{{ route('password.create') }}">Click to Enable</a>
                            </div>
                        @endif
                    </div>
                    <!-- Second Row [END] -->
                    <!-- Content Of Panel [END] -->
                </div>
            </div>
            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-graduation-cap"></i> &thinsp; ATC & Pilot Qualifications

                    <div class="pull-right">
                        <a
                        class="tooltip_displays"
                        href="{{ route('mship.manage.cert.update') }}"
                        data-toggle="tooltip"
                        title="{{ !is_null($_account->cert_checked_at) ? 'Last updated with VATSIM.net ' . $_account->cert_checked_at->diffForHumans() : 'Not yet updated with VATSIM.net.' }} "
                        >
                            <i class="fa fa-sync"></i>
                        </a>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-xs-6 col-lg-6 col-md-12 row-text-contain text-center">
                                    <b>ATC QUALIFICATIONS</b>
                                    <br/>
                                    <small>Showing all achieved</small>
                                </div>
                                <div class="col-xs-6 col-lg-6 col-md-12 text-center">
                                    @foreach($_account->qualifications_atc as $qual)
                                        {{ $qual }}
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                           title="{{ $qual->pivot->created_at }}">
                                            <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                        </a>
                                        <br/>
                                    @endforeach
                                    @if(count($_account->qualifications_atc) < 1)
                                        You have no ATC ratings.
                                    @endif

                                    @foreach($_account->qualifications_atc_training as $qual)
                                        {{ $qual }}
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                           title="{{ $qual->pivot->created_at }}">
                                            <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                        </a>
                                        <br/>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-xs-6 col-lg-6 col-md-12 row-text-contain text-center">
                                    <b>PILOT QUALIFICATIONS</b>
                                    <br/>
                                    <small>Showing all achieved including military</small>
                                </div>
                                <div class="col-xs-6 col-lg-6 col-md-12 text-center">
                                    @foreach($_account->qualifications_pilot as $qual)
                                        {{ $qual }}
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                           title="{{ $qual->pivot->created_at }}">
                                            <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                        </a>
                                        <br/>
                                    @endforeach
                                    @if(count($_account->qualifications_pilot) < 1)
                                        You have no Pilot ratings.
                                    @endif
                                    @foreach($_account->qualifications_pilot_training as $qual)
                                        {{ $qual }}
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                           title="{{ $qual->pivot->created_at }}">
                                            <em>granted {{ $qual->pivot->created_at }}</em>
                                        </a>
                                        <br/>
                                    @endforeach
                                    @foreach($_account->qualifications_pilot_military as $qual)
                                        {{ $qual }}
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                        title="{{ $qual->pivot->created_at }}">
                                            <em>granted {{ $qual->pivot->created_at->diffForHumans() }}</em>
                                        </a>
                                        <br/>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="panel-footer panel-footer-primary">
                    <div class="row">
                        <div class="col-xs-12">
                            <a href="{{ route('mship.manage.cert.update') }}">
                                <span class='fa fa-sync'></span> Details look incorrect? Click here to request an update from VATSIM.net.
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if(!$_account->is_banned)
                <div class="panel panel-ukblue">
                    <div class="panel-heading"><em class="fab fa-teamspeak"></em>
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
                                            [ <strong>Registration #{{ $tsreg->id }}</strong> ]<br/>
                                            <strong>CREATED</strong>:
                                            <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                               title="{{ $tsreg->created_at }}">
                                                <em>{{ $tsreg->created_at->diffForHumans() }}</em>
                                            </a>
                                            <br/>
                                            @if (is_null($tsreg->dbid))
                                                <strong>STATUS</strong>
                                                : <a href="{{ route('teamspeak.new') }}">New Registration</a><br/>
                                            @elseif (!is_null($tsreg->dbid))
                                                <strong>UNIQUE ID</strong>: {{ $tsreg->uid }}<br/>
                                                <strong>LAST IP</strong>: {{ $tsreg->last_ip }}<br/>
                                                <strong>LAST LOGIN</strong>: {{ $tsreg->last_login }}<br/>
                                                <strong>OPERATING SYSTEM</strong>: {{ $tsreg->last_os }}<br/>
                                            @endif
                                            [ <a href="{{ route('teamspeak.delete', [$tsreg->id]) }}">Remove Registration</a>
                                            ]<br/>&nbsp;
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer panel-footer-primary">
                        <div class="row">
                            <div class="col-xs-12">
                                <a href="{{ route('site.community.teamspeak') }}">
                                    <span class='fa fa-info'></span> Teamspeak Guide
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="panel panel-ukblue">
                <div class="panel-heading"><em class="fab fa-discord"></em>
                    Discord Registration
                    <div class="pull-right">
                        @if(!$_account->discord_id)
                            <a href="{{ route("discord.create") }}">
                                <em class="fa fa-plus-circle"></em>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12">
                            @if($_account->discord_id)
                                Currently registered with Discord account {{ $_account->discord_user ? '@' . $_account->discord_user['username'] : $_account->discord_id }}.<br/>
                                <a href="{{ route('discord.destroy') }}">Unlink Discord account</a>
                            @else
                                You are not yet
                                registered.  <a href="{{ route('discord.create') }}">Click here to register.</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="panel-footer panel-footer-primary">
                    <div class="row">
                        <div class="col-xs-12">
                            <a href="{{ route('discord.show') }}">
                                <span class='fa fa-info'></span> Discord Guide
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-ukblue">
                <div class="panel-heading"><i class="fa fa-signal"></i>
                    UK Controller Plugin
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-4">
                            <b>UK CONTROLLER<br/>PLUGIN KEYS</b>
                            @if(count($pluginKeys))
                                <div class="text-center pt-4">
                                    <a class="btn btn-warning btn-sm" href="{{ route('ukcp.token.invalidate') }}">
                                        Invalidate Token(s)
                                    </a>
                                    </br>
                                    <small>
                                        Note: If you are currently online, some operations, such as squawk assignments, will fail.
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="col-xs-8">
                            <div class="row">
                                @forelse($pluginKeys as $key)
                                    <div class="col-xs-6 row-text-contain" style="padding-bottom: 20px;">
                                        [ <strong>Registration {{\App\Libraries\UKCP::getKeyForToken($key)}}</strong>
                                        ]<br/>
                                        <strong>CREATED</strong>:
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                           title="{{ $key->created_at }}">
                                            <em>{{ \Carbon\Carbon::createFromTimeString($key->created_at)->diffForHumans() }}</em>
                                        </a>
                                        <br/>
                                        <strong>EXPIRES</strong>:
                                        <a class="tooltip_displays" href="#" data-toggle="tooltip"
                                           title="{{ $key->expires_at }}">
                                            <em>{{ \Carbon\Carbon::createFromTimeString($key->expires_at)->diffForHumans() }}</em>
                                        </a>
                                        <br/>
                                    </div>
                                @empty
                                    <p>
                                        No keys found.</br>
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-xs-12">
                            The UK Controller Plugin uses a key to identify who is using the plugin. <br/><b>Do not
                                share
                                your keys</b> as actions taken with these keys are logged against your account.
                        </div>
                    </div>
                    <br/>
                    <div class="row">
                        <div class="col-xs-12">
                            When you start EuroScope, the plugin will automatically take you through the process
                            to set up a new key, if required. You can find more information about this process in the
                            UK Controller Plugin guide.
                        </div>
                    </div>
                </div>
                <div class="panel-footer panel-footer-primary">
                    <div class="row">
                        <div class="col-xs-12">
                            <a href="{{ route('ukcp.guide') }}">
                                <span class='fa fa-info'></span> UK Controller Plugin Guide
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
