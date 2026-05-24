@extends('layout')

@section('content')
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			@include('components.html.panel_open', [
				'title' => 'Confirm Secondary Password',
				'icon' => ['type' => 'fa', 'key' => 'fa-key'],
			])
			<p>
				Please confirm your secondary password before continuing.
			</p>

			<form method="POST" action="{{ route('two-factor.confirm-password.store') }}" class="form-horizontal">
				@csrf

				@if ($redirect)
					<input type="hidden" name="redirect" value="{{ $redirect }}">
				@endif

				<div class="form-group">
					<label for="password" class="control-label col-sm-5">Secondary Password</label>
					<div class="col-sm-4">
						<input class="form-control" name="password" type="password" autofocus id="password" required>
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-5">
						<input class="btn btn-default" type="submit" value="Confirm">
					</div>
				</div>
			</form>
			@include('components.html.panel_close')
		</div>
	</div>
@stop
