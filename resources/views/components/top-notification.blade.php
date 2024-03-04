{{-- To disable the use of the top_notification, simply comment out or remove the contents of this file --}}
@if(auth()->user() && ! auth()->user()->discord_id && request()->route()?->uri() != 'discord')
    <div data-top-notification-id="vuk-notification-discord0820" data-top-notification-cookie-expiration-days="2" class="top_notification">
        <div class="container">
            <div class="row">
                <div class="col-md-1 icon text-center">
                    <em class="fab fa-discord"></em>
                </div>
                <div class="col-md-8 message">
                    <p><strong>Join us on Discord, {{ auth()->user()->name_first }}!</strong></p>
                    <p>Discord is available to all VATSIM UK members. Registration takes less than 60 seconds. Simply hit the button to get started.</p>
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


