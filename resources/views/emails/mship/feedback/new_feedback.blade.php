@extends('emails.messages.post')

@section('body')
<p>
    @if($feedback->targeted)
      A new item of {{strtoupper($feedback->formSlug())}} feedback has been submitted for {{ $feedback->account->real_name }}
    @else
      A new item of {{strtoupper($feedback->formSlug())}} feedback has been submitted
    @endif

</p>

<p>
    You may view the feedback by logging on to Core Administration, or by clicking here:</br>
    {!! link_to_route('adm.mship.feedback.view', route('adm.mship.feedback.view', [$feedback->id]), [$feedback->id]) !!}
</p>
@stop
