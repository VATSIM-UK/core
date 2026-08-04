{{-- To disable the use of the top_notification, simply comment out or remove the contents of this file --}}
@if (auth()->user() && !auth()->user()->discord_id && request()->route()?->uri() != 'discord')
	<div data-top-notification-id="vuk-notification-discord0820" data-top-notification-cookie-expiration-days="2"
		class="top_notification">
		<div class="container">
			<div class="row">
				<div class="col-md-1 icon text-center">
					<em class="fab fa-discord"></em>
				</div>
				<div class="col-md-8 message">
					<p><strong>Join us on Discord, {{ auth()->user()->name_first }}!</strong></p>
					<p>Discord is available to all VATSIM UK members. Registration takes less than 60 seconds. Simply
						hit the button to get started.</p>
				</div>
				<div class="col-md-3 cta text-center">
					<a href="{{ route('mship.manage.dashboard') }}" class="button secondary">
						Register for Discord
					</a>
					<a href="#" data-top-notification-id="vuk-notification-discord0820"
						class="button top_notification_dismiss tertiary">
						Remind me in 2 days
					</a>
				</div>
			</div>
		</div>
	</div>
@endif
{{-- Training Panel Notification --}}
@if (auth()->user() && !request()->is('training*'))
	<div data-top-notification-id="vuk-notification-training-beta0126" data-top-notification-cookie-expiration-days="3650"
		class="top_notification">
		<div class="container">
			<div class="row">
				<div class="col-md-1 icon text-center">
					<em class="fas fa-graduation-cap"></em>
				</div>
				<div class="col-md-8 message">
					<p style="margin-bottom: 4px;"><strong>Meet the new Training Panel, {{ auth()->user()->name_first }}!</strong></p>
					<p>We're moving all training management out of CTS and into the Training Panel, it'll eventually replace CTS entirely, so the sooner you get familiar with it, the better. Found a bug or have any feedback? Let us know in #training-panel-feedback-issues on our discord.</p>
				</div>
				<div class="col-md-3 cta text-center">
					<a href="{{ route('filament.training.pages.dashboard') }}" class="button secondary top_notification_open_and_dismiss"
						data-top-notification-id="vuk-notification-training-beta0126">
						Open Training Panel
					</a>
					<a href="#" data-top-notification-id="vuk-notification-training-beta0126" class="button top_notification_dismiss tertiary">Close</a>
				</div>
			</div>
		</div>
	</div>
@endif
