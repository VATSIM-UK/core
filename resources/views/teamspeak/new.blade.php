@extends('layout')

@section('content')

<div class="row">
    @if (array_get($_SERVER, 'REMOTE_ADDR') != $registration->registration_ip)
        <div class="alert alert-danger" role="alert"><strong>Warning!</strong> Your current IP address ({{ array_get($_SERVER, 'REMOTE_ADDR') }}) is different to the IP address you used to create this registration ({{ $registration->registration_ip }}).<br>
            To successfully register, your current IP address must be identical to the one you used to create this registration. <strong>{!! link_to_route('teamspeak.delete', 'Click here', [$registration->id], ['class' => 'alert-link']) !!}</strong> to start a new registration.</div>
    @endif

    {!! Alert::warning(
            "Having trouble with your registration? Please feel free to <a href='https://helpdesk.vatsim.uk' class='alert-link'>contact us</a> with any queries or issues."
        )->withAttributes(["id" => "helpmessage", "style" => "display:none"]) !!}


        <div class="col-md-8 col-md-offset-2">
            {!! HTML::panelOpen("Automatic Registration", ["type" => "fa", "key" => "phone"]) !!}
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            Please click the button below to connect to TeamSpeak.
                            If you have problems with this link, follow the 'Manual' instructions further down the page.
                            Once connected, please wait for your registration to be automatically completed
                        </p>
                    </div>
                    <div class="col-md-12 text-center">
                        <a class="btn btn-primary" href="{{ $auto_url }}">Connect to TeamSpeak</a>
                    </div>
                </div>
            {!! HTML::panelClose() !!}
        </div>

        <div class="col-md-8 col-md-offset-2">
            {!! HTML::panelOpen("Manual Registration", ["type" => "fa", "key" => "phone"]) !!}
                <div class="row">
                    <div class="col-md-6 col-xs-12 col-sm-12">
                        <p>
                        <li>Open TeamSpeak 3</li>
                        <li>Click "Connections" > "Connect"</li>
                        <li>
                            Click the "More" tab so that you are presented with a connection settings screen
                            <blockquote style="font-size: 9pt;">
                                Server Address: {{ $_ENV['TS_HOST'] }}<br />
                                Nickname: {{ $_account->name_first . " " . $_account->name_last }}<br />
                                One-Time Privilege Key: {{ $confirmation->privilege_key }}
                            </blockquote>
                        </li>
                        <li>Fill in the details as shown above, then click "Connect" and wait for your registration to be automatically completed.</li>
                        </p>
                    </div>

                    <div class="col-md-6 hidden-xs hidden-sm">
                        {!! Image::rounded(asset('images/ts_connect.png'), 'Connection Screenshot')->responsive() !!}
                    </div>
                </div>
            {!! HTML::panelClose() !!}
        </div>

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
    if (xmlhttp.responseText == "active") window.location.href = "{{ route('teamspeak.success') }}";
}

window.setInterval(function() {
    requestData("{{ route('teamspeak.status', $registration->id) }}", checkStatus);
}, 5000);

window.setTimeout(function() {
    document.getElementById('helpmessage').style.display = "inherit";
}, 300000);

</script>
@stop
