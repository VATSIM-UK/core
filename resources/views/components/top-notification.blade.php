{{-- To disable the use of the top_notification, simply comment out or remove the contents of this file --}}
@if(!Auth::user()->discord_id)
    <div data-top-notification-id="vuk-notification-discord0820" data-top-notification-cookie-expiration-days="2" class="top_notification">
        <div class="container">
            <div class="row row-eq-height">
                <div class="col-md-1 icon text-center">
                    <i class="fab fa-discord"></i>
                </div>
                <div class="col-md-8 message">
                    <p><b>Discord has arrived. Join us {{Auth::user()->name}}!</b></p>
                    <p>Discord is now available to all VATSIM UK members. Registration takes less than 60 seconds. Simply hit the button to get started. <b>Slack will be closed from Sunday 16th August</b>.</p>
                </div>
                <div class="col-md-3 cta text-center">
                    <a href="{{ route('discord.show') }}" class="button secondary">
                        Register for Discord
                    </a>
                    <a href="#" data-top-notification-id="vuk-notification-discord0820" class="button top_notification_dismiss tertiary">
                        Remind me in 2 days
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif


