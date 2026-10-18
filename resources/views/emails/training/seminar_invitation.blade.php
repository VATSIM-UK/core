@extends('emails.messages.post')

@section('body')
    @if ($invitation->is_short_notice)
        <p>Congratulations! We are pleased to inform you that a 'short notice' place is available for you on the next S1
            Group Seminar, which will be held on {{ $seminar->startsAt()->format('l, j F Y \a\t H:i') }}z.</p>
    @else
        <p>Congratulations! We are pleased to inform you that a place is available for you on the next S1 Group Seminar,
            which will be held on {{ $seminar->startsAt()->format('l, j F Y \a\t H:i') }}z.</p>
    @endif

    <p>In order to confirm your position in this next group session we require you to do the following:</p>
    <ul>
        <li>Respond to this e-mail by clicking the "Accept" button below as soon as possible confirming your attendance to the group
            seminar on the proposed date above. Your response must be received no later than
            <strong>{{ $invitation->expires_at->format('l, j F Y \a\t H:i') }}z</strong>.</li>
        <li>Create a ticket in the <a href="https://helpdesk.vatsim.uk">VATSIM UK Helpdesk</a> with a screenshot of you
            connected to Euroscope using the UK Controller Pack.</li>
    </ul>

    @unless ($invitation->is_short_notice)
        <p><strong>PLEASE NOTE</strong>:</p>

        <p>Due to the extensive wait times for OBS &gt; S1 Training, if you cannot attend this session you will be afforded
            one more opportunity to attend a group session. If you do not accept the second invitation your place on the
            waiting list will be removed.</p>
    @endunless

    <p>If you are having any issues with Euroscope, please ensure you have read the 'readme' within the UK Controller Pack
        or make use of the community help section of the VATUK Discord.</p>

    <p>This seminar will be carried out on the VATSIM UK Teamspeak Server and will include a presentation that will be
        screen shared on the VATSIM UK Discord. Please ensure you have access to <strong>both</strong> prior to the
        session.</p>

    @if ($invitation->is_short_notice)
        <p>Given that this is a 'short notice' offer, you will not be penalised if you are unable to accept; the next
            invitation to a seminar will be treated as your first and you will have one further opportunity to attend
            beyond that.</p>

        <p><strong>If you confirm your attendance to this seminar, but fail to attend, you will be removed from the OBS
                &gt; S1 Waiting List.</strong></p>
    @else
        <p>If you fail to action this e-mail, you will be afforded one more opportunity to attend a seminar.
            <strong>If you fail to action the second invitation, or if you confirm your attendance to this seminar, but
                fail to attend, you will be removed from the OBS &gt; S1 Waiting List.</strong>
        </p>
    @endif

    <p>Finally, if your circumstances change, please ensure you contact us as soon as possible. We will try to accommodate
        your personal circumstances where possible, but we cannot do this after the event.</p>

    <p style="margin-top: 24px;">
        <a href="{{ $notInterestedUrl }}" class="btn"
            style="color: #fff; background-color: #d9534f; border-color: #d43f3a; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #d43f3a; margin-right: 12px;">I am no longer interested</a>
        <a href="{{ $cannotAttendUrl }}" class="btn"
            style="color: #fff; background-color: #f0ad4e; border-color: #eea236; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #eea236; margin-right: 12px;">I am interested but I cannot attend</a>
        <a href="{{ $acceptUrl }}" class="btn"
            style="color: #fff; background-color: #5cb85c; border-color: #4cae4c; text-decoration: none; display: inline-block; padding: 6px 12px; font-size: 14px; border-radius: 4px; border: 1px solid #4cae4c;">Accept Invitation</a>
    </p>
@stop
