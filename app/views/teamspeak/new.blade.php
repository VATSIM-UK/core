@extends('layout')

@section('content')

<p>This page has not yet be completed - buttons, links, features and other material may be inaccurate or not work.</p>

To add: warning if viewing the page where the registration ip is different to the current ip.
{{ $_registration->registration_ip }}

<h3>Automatic <small>Connect via TeamSpeak to register</small></h3>
<p>Please click the button below to connect to TeamSpeak. If you have problems with this link, follow the 'Manual' instructions further down the page. You will receive a Private Message shortly after you enter TeamSpeak with further instructions.</p>
<a class="btn btn-primary" href="ts3server://ts.vatsim-uk.co.uk?nickname=Neil%20Farrington">Connect to TeamSpeak</a>

<h3><small>If the above button does not work for you, please follow the instructions below:</small></h3>
<pre>Show Manual Instructions

Should you need to manually configure TeamSpeak (either due to the link not working, or for advanced users), please follow the steps below to connect.

1: Open Teamspeak 3
2: Click "Connections" > "Connect"
3: Click the "More" tab so that you are presented with a screen similar to the image below

Connection Image
Server Address: ts.vatsim-uk.co.uk
Nickname: {{ $_account->name_first . " " . $_account->name_last }}
One-Time Privilege Key: {{ $_confirmation->privilege_key }}
4: Fill in the details as shown above, then click "Connect"</pre>

<h3>Manual</h3>
<p>Alternatively, please enter your UUID in the box below:</p>




@stop
