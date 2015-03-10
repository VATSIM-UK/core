@extends('layout')

@section('content')
<p class='hidden-xs hidden-sm'>
    Below are the current details stored by the Single Sign-On (SSO) system.
    Please note that as not all data has been transitioned from other (older) systems,
    some data might be recorded incorrectly.
</p>

<table class="table">
    <tr>
        <th class='hidden-xs hidden-sm'>CID</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>CID</strong></span>
            {{ $_account->account_id }}
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>First Name</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>First Name</strong></span>
            {{ $_account->name_first }}
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>Last Name</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Last Name</strong></span>
            {{ $_account->name_last }}
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>Primary Email Address</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Primary Email Address</strong></span>
            <strong>
                {{ $_account->primary_email->email }}
            </strong>
            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $_account->primary_email->created_at }}">
                <em>added {{ $_account->primary_email->created_at }}</em>
            </a>
            @if(count($_account->primary_email->sso_emails) > 0)
                <br />
                <em style="margin-left: 25px;">Assigned to:
                    @foreach($_account->primary_email->sso_emails as $ssoE)
                        {{ $ssoE->sso_account->name }},
                    @endforeach
                </em>
            @endif
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>Secondary Email Addresses</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Secondary Emails</strong></span>
            @foreach($_account->emails as $email)
                @if($email->account_email_id != $_account->primary_email->account_email_id)
                    <strong>
                        {{ $email->email }}
                    </strong>
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $email->created_at }}">
                        <em>added {{ $email->created_at }}</em>
                    </a>
                    @if(count($email->sso_emails) > 0)
                        <br />
                        <em style="margin-left: 25px;">Assigned to:
                            @foreach($email->sso_emails as $ssoE)
                                {{ $ssoE->sso_account->name }},
                            @endforeach
                        </em>
                    @endif
                    <br />
                @endif
            @endforeach
            @if(count($_account->emails) < 2)
                You have no secondary email addresses.
            @endif
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>Second Layer Security</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Second Layer Security</strong></span>
            @if($_account->current_security)
                You currently have second layer security enabled.
                @if($_account->current_security->security->optional)
                    <strong>You are allowed to disable this.</strong>
                @else
                    <strong>You are not allowed to disable this.</strong>
                @endif
            @else
                Second layer security is disabled on your account.
            @endif
            <br />
            <a class="tooltip_displays" href="#" data-toggle="tooltip" title="
               To protect your account further, you can add a secondary password to your account.  You will then be required to enter this password
               after logging in, prior to being granted access to your account or our systems."><em>What is this?</em></a>
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>Last SSO Login</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Last SSO Login</strong></span>
            @if($_account->last_login_ip)
                <strong>{{ $_account->last_login_ip }}</strong>
                <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $_account->last_login }}">
                    <em>{{ $_account->last_login }}</em>
                </a>
            @else
                No login history available.
            @endif
        </td>
    </tr>
    @if(count($_account->qualification_admin) > 0)
        <tr>
            <th class='hidden-xs hidden-sm'>Administrative Ratings<br /><small>Past and Present</small></th>
            <td>
                <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Admin Ratings</strong></span>
                @foreach($_account->qualification_admin as $qual)
                    {{ $qual }}
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                        <em>granted {{ $qual->created_at }}</em>
                    </a>
                    <br />
                @endforeach
            </td>
        </tr>
    @endif
    <tr>
        <th class='hidden-xs hidden-sm'>ATC Qualifications<br /><small>Showing all achieved</small></th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>ATC Ratings</strong></span>
            @foreach($_account->qualifications_atc as $qual)
                    {{ $qual }}
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                        <em>granted {{ $qual->created_at }}</em>
                    </a>
                    <br />
                @endforeach
            @if(count($_account->qualifications_atc) < 1)
                You have no ATC ratings.
            @endif
        </td>
    </tr>
    @if(count($_account->qualifications_atc_training) > 0)
        <tr>
            <th class='hidden-xs hidden-sm'>ATC Training Ratings<br /><small>Past and Present</small></th>
            <td>
                <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>ATC Training Ratings</strong></span>
                @foreach($_account->qualifications_atc_training as $qual)
                    {{ $qual }}
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                        <em>granted {{ $qual->created_at }}</em>
                    </a>
                    <br />
                @endforeach
            </td>
        </tr>
    @endif
    <tr>
        <th class='hidden-xs hidden-sm'>Pilot Qualifications<br /><small>Showing all achieved</small></th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Pilot Ratings</strong></span>
            @foreach($_account->qualifications_pilot as $qual)
                    {{ $qual }}
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                        <em>granted {{ $qual->created_at }}</em>
                    </a>
                    <br />
                @endforeach
            @if(count($_account->qualifications_pilot) < 1)
                You have no Pilot ratings.
            @endif
        </td>
    </tr>
    @if(count($_account->qualifications_pilot_training) > 0)
        <tr>
            <th class='hidden-xs hidden-sm'>Pilot Training Ratings<br /><small>Past and Present</small></th>
            <td>
                <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Pilot Training Ratings</strong></span>
                @foreach($_account->qualifications_pilot_training as $qual)
                    {{ $qual }}
                    <a class="tooltip_displays" href="#" data-toggle="tooltip" title="{{ $qual->created_at }}">
                        <em>granted {{ $qual->created_at }}</em>
                    </a>
                    <br />
                @endforeach
            </td>
        </tr>
    @endif
    <tr>
        <th class='hidden-xs hidden-sm'>Account Status</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>Account Status</strong></span>
            {{ $_account->status_string }} {{ $_account->current_state }}
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>TeamSpeak Registrations @if (count($_account->teamspeak_registrations) < 3)<br /><small>[ {{ link_to_route('teamspeak.new', 'New Registration') }} ]</small>@endif</th>
        <td>
            <span class="hidden-md hidden-lg" style="border-bottom: dashed black 1px; padding-bottom: 2px; margin-bottom: 2px;"><strong>TeamSpeak Registrations @if (count($_account->teamspeak_registrations) < 3)<br /><small>[ {{ link_to_route('teamspeak.new', 'New Registration') }} ]</small>@endif</strong></span>
            @if (count($_account->teamspeak_registrations) == 0)
                    No registrations found.
            @endif
            @foreach ($_account->teamspeak_registrations as $tsreg)
            <div style="float: left; padding-right: 15px;">
                [ Registration #{{ $tsreg->id }} ]<br />
                Created: {{ $tsreg->created_at }}<br />
                @if ($tsreg->status == "new")
                    Status: {{ link_to_route('teamspeak.new', 'New Registration') }}<br />
                @elseif ($tsreg->status == "active")
                    Unique ID: {{ $tsreg->uid }}<br />
                    Last IP: {{ $tsreg->last_ip }}<br />
                    Last login: {{ $tsreg->last_login }}<br />
                    Operating System: {{ $tsreg->last_os }}<br />
                @endif
                [ {{ link_to_route("teamspeak.delete", "Remove Registration", [$tsreg->id]) }} ]<br />&nbsp;
            </div>
            @endforeach
        </td>
    </tr>
    <tr>
        <th class='hidden-xs hidden-sm'>Actions</th>
        <td>
            @if(1 == 2)
                [ <?= HTML::link("mship/auth/logout?override=1", "Cancel Override") ?> ]
            @else
                [ <?= HTML::link("mship/auth/logout/1", "Logout") ?> ]
            @endif

            @if($_account->current_security)
                &nbsp;&nbsp;
                [
                @if($_account->current_security->security->optional)
                    <?= HTML::link("mship/security/replace/1", "Disable") ?> |
                @endif
                <?= HTML::link("mship/security/replace/0", "Modify") ?> Secondary Password
                ]
            @elseif(!$_account->current_security)
                &nbsp;&nbsp;
                [<?= HTML::link("mship/security/enable", "Enable Secondary Password") ?>]
            @endif


            &nbsp;&nbsp;
            [
            @if($_account->is_invisible)
                <?= HTML::link("mship/auth/invisibility", "Disable Invisibility") ?>
            @else
                <?= HTML::link("mship/auth/invisibility", "Enable Invisibility") ?>
            @endif
            ]


            @if(in_array($_account->account_id, array(980234, 1010573, 1104841)))
                &nbsp;&nbsp;
                [ <?= HTML::link("mship/auth/override", "Account Override") ?> ]
            @endif
        </td>
    </tr>
</table>
@stop
