@extends('layout')

@section('content')
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			@include('components.html.panel_open', [
				'title' => 'Two-Factor Authentication',
				'icon' => ['type' => 'fa', 'key' => 'fa-shield'],
			])
			<p>Two-factor authentication is enabled for your account.</p>

			<h4>Recovery Codes</h4>
			<p class="text-muted">Store these recovery codes in a secure location. Each code may only be used once.</p>
			<ul>
				@foreach ($recoveryCodes as $code)
					<li><code>{{ $code }}</code></li>
				@endforeach
			</ul>

			<form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="form-horizontal"
				style="margin-bottom: 1.5em;">
				@csrf
				<input class="btn btn-default" type="submit" value="Regenerate Recovery Codes"
					onclick="return confirm('This will invalidate your existing recovery codes. Continue?');">
			</form>

			@if (!Auth::user()->mandatory_two_factor)
				<form method="POST" action="{{ route('two-factor.disable') }}"
					onsubmit="return confirm('Disable two-factor authentication for this account?');">
					@csrf
					@method('DELETE')
					<input class="btn btn-danger" type="submit" value="Disable Two-Factor Authentication">
				</form>
			@endif

			<p style="margin-top: 1.5em;">
				<a href="{{ route('mship.manage.dashboard') }}">Return to dashboard</a>
			</p>
			@include('components.html.panel_close')
		</div>
	</div>
@stop
