@foreach ($directory->leads as $entry)
	@foreach ($entry->jobTitles as $jobTitle)
		{{-- Escape the title, then turn a newline inside it into a line break. --}}
		<h4 class="text-center">{!! nl2br(e($jobTitle)) !!}</h4>
	@endforeach
	<br />
	<p class="text-center">{{ $entry->name }}</p>
@endforeach

@if (count($directory->team) > 0)
	<h4 class="text-center">{{ $teamHeading }}</h4>
	<table class="table">
		@foreach ($directory->team as $entry)
			<tr>
				<td>
					@foreach ($entry->jobTitles as $jobTitle)
						{{-- Escape the title, then turn a newline inside it into a line break. Add a break between titles, not after the last one. --}}
						{!! nl2br(e($jobTitle)) !!}@if (!$loop->last)
							<br />
						@endif
					@endforeach
				</td>
				<td>{{ $entry->name }}</td>
			</tr>
		@endforeach
	</table>
@endif
