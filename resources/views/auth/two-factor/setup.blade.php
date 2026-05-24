@extends('layout')

@section('content')
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			@include('components.html.panel_open', [
				'title' => 'Two-Factor Authentication Setup',
				'icon' => ['type' => 'fa', 'key' => 'fa-shield'],
			])

			@if (Auth::user()->mandatory_two_factor)
				<p>
					Your account role requires two-factor authentication. You must complete setup before you can access
					VATSIM UK services.
				</p>
			@else
				<p>
					Protect your account with an additional layer of security using an authenticator application.
				</p>
			@endif

			@if ($pendingConfirmation)
				<p>
					Scan the QR code below with your authenticator application, then enter the generated code to finish setup.
				</p>

				<div class="text-center" style="margin: 1.5em 0;">
					{!! Auth::user()->twoFactorQrCodeSvg() !!}
				</div>

				<p class="text-muted">
					If you cannot scan the QR code, configure your application manually using your account email address and
					the secret from your authenticator setup screen.
				</p>

				<form method="POST" action="{{ route('two-factor.confirm') }}" class="form-horizontal">
					@csrf

					<div class="form-group">
						<label for="code" class="control-label col-sm-5">Authentication Code</label>
						<div class="col-sm-4">
							<input class="form-control @error('code', 'confirmTwoFactorAuthentication') has-error @enderror" name="code"
								type="text" inputmode="numeric" value="{{ old('code') }}" autocomplete="one-time-code" id="code"
								required autofocus>
							@error('code', 'confirmTwoFactorAuthentication')
								<span class="help-block">{{ $message }}</span>
							@enderror
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-4 col-sm-offset-5">
							<input class="btn btn-primary" type="submit" value="Confirm and Enable">
						</div>
					</div>
				</form>
			@else
				<p>Click below to generate a QR code for your authenticator application.</p>

				<form method="POST" action="{{ route('two-factor.enable') }}" class="form-horizontal">
					@csrf

					<div class="form-group">
						<div class="col-sm-4 col-sm-offset-5">
							<input class="btn btn-default" type="submit" value="Begin Setup">
						</div>
					</div>
				</form>
			@endif

			@include('components.html.panel_close')
		</div>
	</div>
@stop
