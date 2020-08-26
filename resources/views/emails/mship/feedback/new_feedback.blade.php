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
    You may view the feedback by logging into Nova
</p>
@stop
