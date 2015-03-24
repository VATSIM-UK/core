@extends('layout')

@section('content')

@if (array_get($_SERVER, 'REMOTE_ADDR') != $_registration->registration_ip)
<div class="alert alert-danger" role="alert"><strong>Warning!</strong> Your current IP address ({{ array_get($_SERVER, 'REMOTE_ADDR') }}) is different to the IP address you used to create this registration ({{ $_registration->registration_ip }}).<br>
To successfully register, your current IP address must be identical to the one you used to create this registration. <strong>{{ link_to_route('teamspeak.delete', 'Click here', [$_registration->id], ['class' => 'alert-link']) }}</strong> to start a new registration.</div>
@endif

<div id="helpmessage" class="alert alert-danger" role="alert" style="display:none">Having trouble with your registration? Please feel free to <a href="http://helpdesk.vatsim-uk.co.uk" class="alert-link">contact us</a> with any queries or issues.</div>

<h3>Register via TeamSpeak (Automatic)</h3>
<p>Please click the button below to connect to TeamSpeak. If you have problems with this link, follow the 'Manual' instructions further down the page. Once connected, please wait for your registration to be automatically completed.</p>
<a class="btn btn-primary" href="ts3server://{{ $_ENV['ts.host'] }}?nickname={{ $_account->name_first }}%20{{ $_account->name_last }}&amp;token={{ $_confirmation->privilege_key }}">Connect to TeamSpeak</a>

<h3><small>If the above button does not work for you, please follow the instructions below:</small></h3>
<h3>Register via TeamSpeak (Manual)</h3>

<ol style="margin-left: 40px">
    <li>Open Teamspeak 3</li>
    <li>Click "Connections" > "Connect"</li>
    <li>Click the "More" tab so that you are presented with a screen similar to the image below</li>

    <ul class='list-unstyled' style="margin-left: 20px">
        <li><br>{{ HTML::image('assets/images/ts_connect.png', 'Connection Screenshot', ['style' => 'box-shadow: 10px 10px 35px #777']) }}</li>
        <li><br>Server Address: {{ $_ENV['ts.host'] }}</li>
        <li>Nickname: {{ $_account->name_first . " " . $_account->name_last }}</li>
        <li>One-Time Privilege Key: {{ $_confirmation->privilege_key }}</li>
        <li>&nbsp;</li>
    </ul>

    <li>Fill in the details as shown above, then click "Connect" and wait for your registration to be automatically completed.</li>
</ol>

<script type="text/javascript">
function requestData(url, callback) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4) {
            callback(xmlhttp);
        }
    }
    xmlhttp.open("POST", url, true);
    xmlhttp.send(null);
}

function checkStatus(xmlhttp) {
    if (xmlhttp.responseText == "active") window.location.href = "{{ route('teamspeak.success') }}";
}

window.setInterval(function() {
    requestData("{{ route('teamspeak.status', $_registration->id) }}", checkStatus);
}, 5000);

window.setTimeout(function() {
    document.getElementById('helpmessage').style.display = "inherit";
}, 300000);

</script>
@stop
