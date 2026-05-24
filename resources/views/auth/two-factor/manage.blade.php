@extends('layout')

@section('styles')
	<style type="text/css">
		.two-factor-recovery-codes-well {
			display: block;
			max-width: 100%;
			margin-bottom: 0.75em;
			text-align: left;
		}

		.two-factor-recovery-codes {
			margin: 0;
			padding: 0.75em 1em;
			font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			font-size: 0.95em;
			line-height: 1.6;
			color: #333;
			background-color: transparent;
			border: none;
			white-space: pre-wrap;
			word-break: break-all;
		}

		.two-factor-recovery-codes-actions {
			margin-bottom: 1.5em;
		}

		.two-factor-recovery-codes-action {
			display: inline;
			margin-right: 1em;
		}

		.two-factor-recovery-codes-action:last-of-type {
			margin-right: 0;
		}

		.two-factor-recovery-codes-copied {
			margin-left: 0.75em;
		}

		[x-cloak] {
			display: none !important;
		}
	</style>
	@include('auth.two-factor.partials.setup-faqs-styles')
@endsection

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

			@include('auth.two-factor.partials.why-backup-codes-matter')

			@include('auth.two-factor.partials.recovery-codes-display', ['recoveryCodes' => $recoveryCodes])

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
