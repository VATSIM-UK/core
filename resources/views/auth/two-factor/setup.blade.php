@extends('layout')

@section('styles')
	<style type="text/css">
		.two-factor-setup-secret-well {
			display: inline-block;
			max-width: 100%;
			margin-bottom: 0.75em;
			text-align: left;
		}

		.two-factor-setup-secret {
			display: inline-block;
			padding: 0.5em 0.75em;
			font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
			font-size: 1.15em;
			letter-spacing: 0.12em;
			color: #333;
			background-color: #f9f2f4;
			border: 1px solid #ddd;
			word-break: break-all;
		}

		[x-cloak] {
			display: none !important;
		}

		.two-factor-setup-faqs {
			margin-top: 1.5em;
		}

		.two-factor-setup-faqs-toggle {
			display: block;
			width: 100%;
			margin: 0;
			padding: 0;
			text-align: left;
			background: none;
			border: none;
			color: inherit;
			cursor: pointer;
		}

		.two-factor-setup-faqs-toggle:hover,
		.two-factor-setup-faqs-toggle:focus {
			color: inherit;
			text-decoration: none;
			outline: none;
		}

		.two-factor-setup-faqs-chevron {
			display: inline-block;
			margin-right: 0.35em;
			transition: transform 0.2s ease;
		}

		.two-factor-setup-faqs-chevron--expanded {
			transform: rotate(90deg);
		}

		.two-factor-setup-faq:last-child p:last-child {
			margin-bottom: 0;
		}

		@media (min-width: 992px) {
			.two-factor-setup-row {
				display: flex;
				align-items: flex-start;
			}

			.two-factor-setup-main {
				border-right: 1px solid #ddd;
				padding-right: 2em;
			}

			.two-factor-setup-qr {
				display: flex;
				flex-direction: column;
				align-items: center;
				padding-left: 2em;
			}
		}

		@media (max-width: 991px) {
			.two-factor-setup-qr {
				margin-top: 1.5em;
				padding-top: 1.5em;
				border-top: 1px solid #ddd;
			}
		}
	</style>
@endsection

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
					Scan the QR code with your authenticator application, then enter the generated code to finish setup.
				</p>

				<div class="row two-factor-setup-row">
					<div class="col-md-7 two-factor-setup-main">
						<form method="POST" action="{{ route('two-factor.confirm') }}" class="form-horizontal">
							@csrf

							<div class="form-group">
								<label for="code" class="control-label col-sm-4">Authentication Code</label>
								<div class="col-sm-8">
									<input class="form-control @error('code', 'confirmTwoFactorAuthentication') has-error @enderror" name="code"
										type="text" inputmode="numeric" value="{{ old('code') }}" autocomplete="one-time-code" id="code"
										required autofocus>
									@error('code', 'confirmTwoFactorAuthentication')
										<span class="help-block">{{ $message }}</span>
									@enderror
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-8 col-sm-offset-4">
									<input class="btn btn-primary" type="submit" value="Confirm and Enable">
								</div>
							</div>
						</form>

						@include('auth.two-factor.partials.why-mfa-required')
						@include('auth.two-factor.partials.mfa-explainer')
						@include('auth.two-factor.partials.authenticator-faqs')
					</div>

					<div class="col-md-5 text-center two-factor-setup-qr">
						{!! Auth::user()->twoFactorQrCodeSvg() !!}
						<p class="text-muted" style="margin-top: 0.75em; margin-bottom: 0;">Scan with your authenticator app</p>

						<div style="margin-top: 1.5em;" x-data="{
	    copied: false,
	    copySecret() {
	        navigator.clipboard.writeText(this.$refs.secretKey.textContent.trim()).then(() => {
	            this.copied = true;
	            setTimeout(() => { this.copied = false; }, 2000);
	        });
	    },
	}">
							<p class="text-muted" style="margin-bottom: 0.5em;">
								Cannot scan the QR code? Enter this setup code manually in your authenticator application.
							</p>
							<div class="well well-sm two-factor-setup-secret-well">
								<code x-ref="secretKey" class="two-factor-setup-secret">{{ Auth::user()->twoFactorSecretKey() }}</code>
							</div>
							<div>
								<button type="button" class="btn btn-link" style="padding-left: 0;" @click.prevent="copySecret()">
									<i class="fa fa-copy"></i> Copy to clipboard
								</button>
								<span x-show="copied" x-cloak class="text-success" style="display: none; margin-left: 0.5em;">Copied!</span>
							</div>
						</div>
					</div>
				</div>
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
