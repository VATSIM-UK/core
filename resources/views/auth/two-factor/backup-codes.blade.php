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
	</style>
	@include('auth.two-factor.partials.setup-faqs-styles')
@endsection

@section('content')
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			@include('components.html.panel_open', [
				'title' => 'Save Your Recovery Codes',
				'icon' => ['type' => 'fa', 'key' => 'fa-key'],
			])

			<p>
				Two-factor authentication is now enabled. Save these recovery codes before continuing — each code can
				only be used once if you lose access to your authenticator application.
			</p>

			@include('auth.two-factor.partials.why-backup-codes-matter')

			@include('auth.two-factor.partials.recovery-codes-display', ['recoveryCodes' => $recoveryCodes])

			<p class="text-muted">
				Keep these codes somewhere secure, such as a password manager. Do not store them in the same place as
				your authenticator application.
			</p>

			<a href="{{ route('mship.manage.dashboard') }}" class="btn btn-primary">Continue to dashboard</a>

			@include('components.html.panel_close')
		</div>
	</div>
@stop
