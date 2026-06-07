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

    protected ?Discord $discord = null;

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

        $this->discord = $discord;

        $discord->on('init', function (Discord $discord) {
            $this->honeypotStartup();

            $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
                if ($message->author->bot) {
                    return;
                }

                if ((string) $message->channel_id === (string) config('services.discord.honeypot_channel_id')) {
                    HandleHoneypotTrigger::dispatch(
                        discordUserId: $message->author->id,
                        discordUsername: $message->author->username,
                        messageId: $message->id,
                    );
                }
            });
        });

        $discord->run();
    }

    public function honeypotStartup()
    {
        $honeypotChannelId = config('services.discord.honeypot_channel_id');
        $honeypotChannel = $this->discord->getChannel($honeypotChannelId);
        if (! $honeypotChannel) {
            $this->error('Honeypot channel not found');

            return;
        }

        // catch up with any old messages
        $coreDiscord = app()->make(\App\Libraries\Discord::class);
        $messages = $coreDiscord->getChannelMessages($honeypotChannelId, 100);

        foreach ($messages as $message) {
            if (empty($message['author']['bot'])) {
                HandleHoneypotTrigger::dispatch(
                    discordUserId: $message['author']['id'],
                    discordUsername: $message['author']['username'],
                    messageId: $message['id'],
                );
            }
        }
    }
}
