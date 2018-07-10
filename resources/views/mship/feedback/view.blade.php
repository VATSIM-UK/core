@extends('layout')

@section('content')
<div class="panel panel-ukblue">
	<div class="panel-heading"> My Feedback</div>
	<div class="panel-body">
        @foreach($feedback as $item)
            <b>Type: </b> {{ $item->form->name }}<br />
			<b>Feedback Left: </b> {{ $item->created_at->format('d M Y') }}<br />
			@if($item->comment)
			<b>Comment: </b> {{ $item->sent_comment }}<br />
			<b>Comment By: </b> {{ $item->sender->real_name }}
			@else
			<b>Comment: </b> The training department wanted to make you aware of some positive feedback that was left for you.<br /><br />
			@endif
        @endforeach
    </div>
</div>

@stop