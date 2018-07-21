@extends('layout')

@section('content')
<div class="panel panel-ukblue">
	<div class="panel-heading"> My Feedback</div>
	<div class="panel-body">
		<p>Here, you can view feedback that has been sent to you by the VATSIM UK training departments.</p>

		<table class="table">
			<tr>
				<th>Type</th>
				<th>Feedback Left At</th>
				<th>Message</th>
				<th>Message From</th>
			</tr>
			@foreach($feedback as $item)
				<tr>
					<td>{{ $item->form->name }}</td>
					<td>{{ $item->created_at->format('d M Y') }}</td>
					@if($item->sent_comment)
						<td>{{ $item->sent_comment }}</td>
						<td>{{ $item->sender->real_name }} ({{ $item->sender->id }})</td>
					@else
						<td>The training department wanted to make you aware of some positive feedback that was left for you.</td>
						<td>Automatic</td>
				</tr>
				@endif
			@endforeach
		</table>

    </div>
</div>

@stop