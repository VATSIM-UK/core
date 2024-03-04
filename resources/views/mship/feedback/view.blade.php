@extends('layout')

@section('content')
<div class="panel panel-ukblue">
	<div class="panel-heading"> My Feedback</div>
	<div class="panel-body">
		<p>Here, you can view feedback that has been sent to you by the VATSIM UK staff team.</p>

		<table class="table">
			<tr>
				<th>Type</th>
				<th>Feedback Left At</th>
				<th>Callsign</th>
				<th>Message</th>
			</tr>
			@foreach($feedback as $item)
				<tr>
					<td>{{ $item->form->name }}</td>
					<td>{{ $item->created_at->format('d M Y') }}</td>
					@if($item->position && $item->position->response !== null)
						<td>{{ $item->position->response }}</td>
					@else
						<td>N/A</td>
				@endif
					@if($item->sent_comment)
						<td>{{ $item->sent_comment }}</td>
					@else
						<td>The staff team wanted to make you aware of some positive feedback that was left for you.</td>
				</tr>
				@endif
			@endforeach
		</table>

    </div>
</div>

@stop