@extends('emails.messages.post')

@section('body')

<p>Dear {{ $account->name_first }},</p>

<p>We write to inform you that you have been removed from your place on the ATC Training {{  $waiting_list->name }} Waiting List. 
    At the time of your removal, you had an outstanding offer of a training place and, accordingly, 
    that offer has been rescinded - you may no longer accept that training place. 
    Whilst this will be disappointing, there are few reasons why this would occur, which include:</p>

<p>
- Failing to remain active on the UK ATC Roster
- Failing to respond to communications from the ATC Training Team
- A suspension from VATSIM UK's services
- A suspension from the VATSIM Network
</p>

<p>If you wish to query the specific details, appeal this matter or otherwise ask any questions, please raise a ticket for the attention of the ATC Training Team at the helpdesk.</p>

<p>Many thanks,<br>
The ATC Training Team</p>

@stop