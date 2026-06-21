@extends('emails.messages.post')

@section('body')
    <p>Thank you for accepting a mentoring session for VATSIM UK. Details of your accepted session are shown below:</p>

    <ul>
        <li>
            <strong>Student</strong>:
            <a href="{{ \App\Filament\Training\Pages\Mentor\MentoringHistory::getUrl(parameters: ['tableFilters' => ['student' => ['value' => $studentCid]]], panel: 'training') }}">
                {{ $studentName }}
            </a>
            ({{ $studentCid }})
        </li>
        <li><strong>Position:</strong> {{ $position }}</li>
        <li><strong>Date/Time</strong>: {{ $sessionDateTime }}</li>
    </ul>

    <p>Please note that the times displayed are in Zulu/GMT.</p>

    <p>Your student has been sent an e-mail reminding them to prepare for the session, including referring to relevant reference material and previous mentoring session reports. If they have failed to do so, please inform the TGI and consider whether the session should be delayed.</p>

    <p>To stop receiving these alerts, you can amend your email settings in the STUDENT menu.</p>
@stop

@section('signature')
    VATSIM UK Training Department
@stop
