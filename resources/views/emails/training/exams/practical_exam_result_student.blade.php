@extends('emails.messages.post')

@section('body')
<p>
Your {{ $examType }} practical exam has been completed and the result is now available.
</p>

<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0; color: #333;">Exam Result: <strong>{{ $result }}</strong></h3>
</div>

<h4>Exam Details:</h4>
<ul>
    <li><strong>Exam Type:</strong> {{ $examType }}</li>
    <li><strong>Position:</strong> {{ $position }}</li>
    <li><strong>Examiner:</strong> {{ $examiner }}</li>
    <li><strong>Date:</strong> {{ $date }}</li>
</ul>

@if($practicalResult->notes)
<h4>Examiner Comments:</h4>
<div style="background-color: #f8f9fa; padding: 10px; border-radius: 3px; margin: 10px 0;">
    {{ $practicalResult->notes }}
</div>
@endif

@if($result === 'Passed')
<p style="color: #28a745; font-weight: bold;">
    Congratulations! You have successfully passed your {{ $examType }} practical exam.
    Your new rating will be processed shortly.
</p>
@elseif($result === 'Failed')
<p style="color: #dc3545;">
    Unfortunately, you did not pass your {{ $examType }} practical exam this time.
    You will need to undergo further training before you can sit this exam again.
    The Training Department will contact you to discuss your next steps.
</p>
@else
<p>
    Your exam result has been recorded as {{ $result }}. Please contact the training department if you have any questions.
</p>
@endif

<p>
If you have any questions about your exam result, please don't hesitate to contact the training department.
</p>

@stop
