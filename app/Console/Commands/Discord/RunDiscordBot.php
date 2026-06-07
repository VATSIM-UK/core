<?php

declare(strict_types=1);

namespace App\Console\Commands\Discord;

use App\Jobs\Discord\HandleHoneypotTrigger;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Illuminate\Console\Command;

class RunDiscordBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the Discord WebSocket client to listen for events.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Discord Gateway...');

        $discord = new Discord([
            'token' => config('services.discord.token'),
            'intents' => Intents::getDefaultIntents(),
        ]);

        $discord->on('init', function (Discord $discord) {
            $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
                if ($message->author->bot) {
                    return;
                }

                if ((string) $message->channel_id === (string) config('services.discord.honeypot_channel_id')) {
                    HandleHoneypotTrigger::dispatch(
                        discordUserId: (string) $message->author->id,
                        discordUsername: $message->author->username,
                    );
                }
            });
        });

        $discord->run();
    }
}
