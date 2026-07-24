@extends('emails.messages.post')

@section('body')
    @if ($ban->period_finish)
        <p>
            Your current suspension of access to all of the VATSIM United Kingdom services (Forum/TeamSpeak/CT System) has been
            changed to a total length of {{ $ban_total_length }} (including time served).
        </p>
    @else
        <p>
            Your current suspension of access to all of the VATSIM United Kingdom services (Forum/TeamSpeak/CT System) has been
            changed to a <strong>permanent</strong> suspension.
        </p>
    @endif

    @if ($ban->reason_extra)
        <p>
            The following reason has been given:
            <br />
            {!! nl2br($ban->reason_extra) !!}
        </p>
    @endif

    @if ($ban->period_finish)
        <p>
            Your account will automatically regain access to all of the VATSIM United Kingdom services at
            {{ $ban->period_finish->format('l jS \\of F Y H:i:s \\z') }}.
            Please do not attempt to access any services before this time.
        </p>
    @else
        <p>
            This is a <strong>permanent</strong> ban. Your account will not automatically regain access to VATSIM United Kingdom services.
        </p>
    @endif

    @if ($ban->is_local)
        <p>
            <strong>This ban only applies to VATSIM UK services. You will be notified separately if you are also banned from
                network services.</strong>
        </p>
    @endif
@stop
