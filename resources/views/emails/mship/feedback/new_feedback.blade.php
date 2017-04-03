<p>
    A new item of {{strtoupper($feedback->facilityName())}} feedback as been submitted for {{ $feedback->account->real_name }}
</p>

<p>
    You may view the feedback by loging on to Core Administration, or by clicking here:</br>
    {!! link_to_route('adm.mship.feedback.view', route('adm.mship.feedback.view', [$feedback->id]), [$feedback->id]) !!}
</p>
