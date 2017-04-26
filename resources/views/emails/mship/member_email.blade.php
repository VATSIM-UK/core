@extends('emails.messages.post')

@section('body')

    <p style="font-weight: bold;">The following message was sent by {{$sender->name}} ({{$sender->id}}). Please report
        abuse <a href="mailto:web-support@vatsim.uk?subject=Abuse: Membership Email Functionality" data-toggle="tooltip" title="Report Abuse" style="color: #00b0f0;">here</a>.

    <p class="well">{!! nl2br(strip_tags($messageContent)) !!}</p>

    <p>
        @if ($replyAllowed === true)
            You can reply directly to this email.
        @else
            To reply, please visit {{link_to_route('mship.email')}}.
        @endif
    </p>

@stop
