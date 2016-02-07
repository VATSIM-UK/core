@extends('layout')

@section('content')


<div id="helpmessage" class="alert alert-danger" role="alert" style="display:none">Having trouble with your registration? Please feel free to <a href="http://helpdesk.vatsim-uk.co.uk" class="alert-link">contact us</a> with any queries or issues.</div>

<h3>Create Slack Account</h3>
<p>
    We have integrated as much of the registration process as possible, however there are still a few manual steps.  You should be up and running within less than 60 seconds!
</p>

<ol style="margin-left: 40px">
    <li>An email invite has been sent to you from Slack.com (please check your junk mail).</li>
    <li>Follow the registration instructions in that email for http://vatsim-uk.slack.com ensuring your FULL NAME is {{ $_account->name }}</li>
    <li>Enter the command (inclusive of slash) <blockquote>/register {{ $slackToken }}</blockquote></li>
    <li>You will receive a confirmation message from "Slackbot" that your registration is successful</li>
</ol>

<p><strong>It is highly important that you do not share your registration code with anyone else to ensure your account is not compromised.</strong></p>

<script type="text/javascript">
function requestData(url, callback) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4) {
            callback(xmlhttp);
        }
    }
    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("X-CSRF-TOKEN", "{{csrf_token()}}");
    xmlhttp.send(null);
}

function checkStatus(xmlhttp) {
    if (xmlhttp.responseText == "active") window.location.href = "{{ route('slack.success') }}";
}

window.setInterval(function() {
    requestData("{{ route('slack.status', $slackToken->token_id) }}", checkStatus);
}, 5000);

window.setTimeout(function() {
    document.getElementById('helpmessage').style.display = "inherit";
}, 300000);

</script>
@stop
