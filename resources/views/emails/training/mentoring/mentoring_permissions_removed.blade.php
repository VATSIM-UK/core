@extends('emails.messages.post')

@section('body')
    An automated review of mentoring history within {{ $category }} has identified that you have not mentored within the previous six months. Accordingly, we have removed your rights to mentor within the training group.
    <br><br>
    Should you wish to continue mentoring, you'll more than likely be welcomed back; please raise a ticket in <a href="https://helpdesk.vatsim.uk">the helpdesk</a> for the attention of the ATC Training team and we'll get back to you as soon as we can.
    <br><br>
    Thank you for your help mentoring within VATSIM UK; whether you mentored once or a hundred times a month, every session helps us deliver training. We really couldn't have done it without you.
@stop

@section('signature')
    The ATC Training Team
@stop
