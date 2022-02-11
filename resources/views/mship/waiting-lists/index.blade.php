@extends('layout')

@section('content')
<div>
    <p>This page shows the waiting lists you are currently in. Please note that any flags or criteria for being eligible on a waiting list are only calculated every 24 hours.</p>
    <p>If you aren't on a waiting list and would like to join one, please contact the appropriate department on the <a href="https://helpdesk.vatsim.uk/">helpdesk</a>.</p>
</div>
@include('mship.waiting-lists._waiting_lists_list', ["title"=> "ATC Waiting Lists", "icon" => "fa-headset", "waitingLists" => $atcLists])
@include('mship.waiting-lists._waiting_lists_list', ["title"=> "Pilot Waiting Lists", "icon" => "fa-plane", "waitingLists" => $pilotLists])
@endsection
