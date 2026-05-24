@php
	$challengeAccount = session('login.id') ? \App\Models\Mship\Account::find(session('login.id')) : null;
@endphp

@extends('layout')

@section('styles')
	<style>
		@keyframes two-factor-autofill-start {
			from {}

			to {}
		}

		#code:-webkit-autofill {
			animation-name: two-factor-autofill-start;
			animation-duration: 0.001s;
		}
	</style>
@endsection

@section('content')
	<div class="row" x-data="{
    useRecovery: @js($errors->has('recovery_code')),
    submitting: false,
    _submitCheckTimer: null,
    syncCodeFromInput() {
        const input = this.$refs.codeInput;
        const digits = input.value.replace(/\D/g, '');
        if (input.value !== digits) {
            input.value = digits;
        }
        return digits;
    },
    maybeSubmitAuthenticator() {
        if (this.submitting) {
            return;
        }
        if (this.syncCodeFromInput().length !== 6) {
            return;
        }
        this.submitting = true;
        this.$refs.authenticatorForm.requestSubmit();
    },
    scheduleSubmitCheck() {
        clearTimeout(this._submitCheckTimer);
        this._submitCheckTimer = setTimeout(() => this.maybeSubmitAuthenticator(), 75);
    },
    onWebkitAutofill(event) {
        if (event.animationName === 'two-factor-autofill-start') {
            this.scheduleSubmitCheck();
        }
    },
    showRecovery() {
        this.useRecovery = true;
        this.$nextTick(() => this.$refs.recoveryCode?.focus());
    },
    showAuthenticator() {
        this.useRecovery = false;
        this.submitting = false;
        this.$nextTick(() => this.$refs.codeInput?.focus());
    },
}">
		<div class="col-md-8 col-md-offset-2">
			@include('components.html.panel_open', [
				'title' => 'Two-Factor Authentication',
				'icon' => ['type' => 'fa', 'key' => 'fa-shield'],
			])

			<p x-show="!useRecovery">
				Enter the authentication code from your authenticator application to continue.
			</p>

			<template x-if="useRecovery">
				<p>Enter one of your unused recovery codes to continue.</p>
			</template>

			<form x-ref="authenticatorForm" x-show="!useRecovery" method="POST" action="{{ route('two-factor.login.store') }}"
				class="form-horizontal" autocomplete="on" @submit="submitting = true">
				@csrf

				@if ($challengeAccount)
					<input type="text" name="username" autocomplete="username" value="{{ $challengeAccount->cid }}" tabindex="-1"
						aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;padding:0;border:0;">
				@endif

				<div class="form-group">
					<label for="code" class="control-label col-sm-5">Authentication Code</label>
					<div class="col-sm-4">
						<input x-ref="codeInput" name="code" type="text" inputmode="numeric"
							class="form-control @error('code') has-error @enderror" id="code" value="{{ old('code') }}"
							autocomplete="one-time-code" autocapitalize="off" autocorrect="off" spellcheck="false"
							aria-label="One-time verification code" enterkeyhint="go" @input="scheduleSubmitCheck()"
							@paste="scheduleSubmitCheck()" @animationstart="onWebkitAutofill($event)" :disabled="submitting" autofocus>
						@error('code')
							<span class="help-block">{{ $message }}</span>
						@enderror
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-7 col-sm-offset-5">
						<button type="submit" class="btn btn-primary" :disabled="submitting">
							<span x-show="!submitting">Continue</span>
							<span x-show="submitting" x-cloak>Verifying…</span>
						</button>
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-7 col-sm-offset-5">
						<p class="form-control-static">
							<a href="#" @click.prevent="showRecovery()">Lost access to your authenticator? Use a recovery code</a>
						</p>
					</div>
				</div>
			</form>

			<template x-if="useRecovery">
				<form method="POST" action="{{ route('two-factor.login.store') }}" class="form-horizontal">
					@csrf

					<div class="form-group">
						<label for="recovery_code" class="control-label col-sm-5">Recovery Code</label>
						<div class="col-sm-4">
							<input x-ref="recoveryCode" class="form-control @error('recovery_code') has-error @enderror" name="recovery_code"
								type="text" value="{{ old('recovery_code') }}" autocomplete="off" id="recovery_code">
							@error('recovery_code')
								<span class="help-block">{{ $message }}</span>
							@enderror
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-7 col-sm-offset-5">
							<button type="submit" class="btn btn-primary">Continue</button>
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-7 col-sm-offset-5">
							<p class="form-control-static">
								<a href="#" @click.prevent="showAuthenticator()">Use your authenticator application instead</a>
							</p>
						</div>
					</div>
				</form>
			</template>

			@include('components.html.panel_close')
		</div>
	</div>
@stop
