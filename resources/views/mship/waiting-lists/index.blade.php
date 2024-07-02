@extends('layout')

@section('content')
<div>
    <p>This page shows the waiting lists you are currently in. Please note that any flags or criteria for being eligible on a waiting list are only calculated every 24 hours.</p>
    <p>You will only show on a waiting list if you meet the activity and experience hour requirements as set out in ATC Training Handbook section 5.12.1, 5.13.1 and 5.14.1. If your position shows this does NOT guarantee eligibility for a training place. You must have completed the relevant CTS Theory exam in order to be offered a training place. The full criteria can be found in the ATC Training Handbook sections linked above.</p>
    <p>If you aren't on a waiting list and would like to join one, please contact the appropriate department on the <a href="https://helpdesk.vatsim.uk/">helpdesk</a>.</p>
</div>


@if($isOBS)
    <div class="alert alert-info">
        <p>Note: If you are in the OBS->S1 waiting list, this will <strong>not</strong> be shown here as it uses a different system.</p>
    </div>
@endif

@include('mship.waiting-lists._waiting_lists_list', ["title"=> "ATC Waiting Lists", "icon" => "fa-headset", "waitingLists" => $atcLists])

@include('mship.waiting-lists._waiting_lists_list', ["title"=> "Pilot Waiting Lists", "icon" => "fa-plane", "waitingLists" => $pilotLists])
@endsection
