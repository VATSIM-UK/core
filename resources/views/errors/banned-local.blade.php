@extends('layout', ['shellOnly' => true])

@section('content')
    <h1>You are currently suspended from VATSIM UK systems.</h1>
    <h2>Reason</h2>
    <p>{{ $ban->reason->reason_text }}
    <h3>Since</h2>
    <p>{{ $ban->period_start->toDayDateTimeString() }} ({{ $ban->period_start->diffForHumans() }}).</p>
    <h3>Expiry</h2>
    <p>Your ban is due to expire on {{ $ban->period_finish->toDayDateTimeString() }} UTC ({{ $ban->period_finish->diffForHumans() }})</p>
    <p>If you believe this to be an error, please contact the VATSIM UK Community team at <a href="https://helpdesk.vatsim.uk">https://helpdesk.vatsim.uk</a> who will be able to assist further.</p>
@stop
