@extends('layout')

@section('content')
	@if ($account->qualification_atc?->isS1)
		<div class="alert alert-danger">
			<h3 style="margin-top: 0">Heathrow Ground Endorsement - S1</h3>
			<p>
				This page only applies to controllers with a S2+ controller rating. As an S1 controller, you can view
				information on the S1 Heathrow Ground Endorsement <a
					href="{{ route('controllers.endorsements.heathrow_ground_s1') }}">here</a>.
			</p>
		</div>
	@endif

	<div class="row">
		<div class="col-md-9">
			<div class="panel panel-ukblue">
				<div class="panel-heading"><i class="glyphicon glyphicon-pencil"></i> &thinsp;Heathrow Endorsements
				</div>
				<div class="panel-body">
					@include('site.atc.heathrow-content')
				</div>
			</div>
		</div>

		<div class="col-md-3">
			<div class="panel panel-ukblue">
				<div class="panel-heading"><i class="glyphicon glyphicon-question-sign"></i> &thinsp; FAQs
				</div>
				<div class="panel-body">
					@include('site.atc.heathrow-faqs')
				</div>
			</div>
		</div>
	</div>

	@if (!empty($endorsementProgress))
		<div class="row">
			<div class="col-md-9">
				<div class="panel panel-ukblue">
					<div class="panel-heading"><i class="fa fa-info"></i> &thinsp; Your Progress</div>
					<div class="panel-body">
						@foreach ($endorsementProgress as $ep)
							<h4>{{ $ep['name'] }}</h4>
							@foreach ($ep['bars'] as $bar)
								@php $met = $bar['hours'] >= $bar['required']; @endphp
								<p><strong>{{ $bar['label'] }}</strong>
								<div class="progress" data-toggle="tooltip" title="{{ $bar['label'] }}">
									<div class="progress-bar {{ $met ? 'progress-bar-success' : 'progress-bar-warning' }}" role="progressbar"
										style="width: {{ min(($bar['hours'] / $bar['required']) * 100, 100) }}%" aria-valuemin="0"
										aria-valuemax="{{ $bar['required'] }}">
										{{ floor($bar['hours']) }} / {{ $bar['required'] }} Hrs
									</div>
								</div>
							@endforeach
							@if (!$loop->last)
								<hr>
							@endif
						@endforeach
					</div>
				</div>
			</div>
		</div>
	@endif
@stop
