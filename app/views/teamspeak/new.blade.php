@extends('layout')

@section('content')

@if (array_get($_SERVER, 'REMOTE_ADDR') != $_registration->registration_ip)
<div class="alert alert-danger" role="alert"><strong>Warning!</strong> Your current IP address ({{ array_get($_SERVER, 'REMOTE_ADDR') }}) is different to the IP address you used to create this registration ({{ $_registration->registration_ip }}).<br>
To successfully register, your current IP address must be identical to the one you used to create this registration. <strong>{{ link_to_route('teamspeak.forcenew', 'Click here', [], ['class' => 'alert-link']) }}</strong> to start a new registration.</div>
@endif

<h3>Register via TeamSpeak (Automatic)</h3>
<p>Please click the button below to connect to TeamSpeak. If you have problems with this link, follow the 'Manual' instructions further down the page. You will receive a Private Message shortly after you enter TeamSpeak with further instructions.</p>
<a class="btn btn-primary" href="ts3server://ts.vatsim-uk.co.uk?nickname={{ $_account->name_first . "%20" . $_account->name_last }}">Connect to TeamSpeak</a>

<h3><small>If the above button does not work for you, please follow the instructions below:</small></h3>
<h3>Register via TeamSpeak (Manual)</h3>
<!--h4><span style="cursor: pointer; text-decoration: none" onclick="document.getElementById('manual-connection').style.display='block'">Click here to show TeamSpeak connection instructions.</a></h4-->

<div id="manual-connection" class="not-a-well" style="display: block">

    <ol style="margin-left: 40px">
        <li>Open Teamspeak 3</li>
        <li>Click "Connections" > "Connect"</li>
        <li>Click the "More" tab so that you are presented with a screen similar to the image below</li>

        <ul class='list-unstyled' style="margin-left: 20px">
            <li><br>{{ HTML::image('assets/images/ts_connect.png', 'Connection Screenshot') }}</li>
            <li><br>Server Address: ts.vatsim-uk.co.uk</li>
            <li>Nickname: {{ $_account->name_first . " " . $_account->name_last }}</li>
            <li>One-Time Privilege Key: {{ $_confirmation->privilege_key }}</li>
            <li>&nbsp;</li>
        </ul>

        <li>Fill in the details as shown above, then click "Connect"</li>
    </ol>

</div>

<h3>Register Manually</h3>
<p>Alternatively, please enter your UUID in the box below:</p>




@stop
