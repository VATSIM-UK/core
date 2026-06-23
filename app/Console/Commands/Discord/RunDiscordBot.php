<?php

declare(strict_types=1);

namespace App\Console\Commands\Discord;

use App\Jobs\Discord\HandleHoneypotTrigger;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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

                $this->cacheMessage(
                    discordUserId: $message->author->id,
                    channelId: $message->channel_id,
                    messageId: $message->id,
                );

                if ($message->channel_id === config('services.discord.honeypot_channel_id')) {
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
                $this->cacheMessage(
                    discordUserId: $message['author']['id'],
                    channelId: $honeypotChannelId,
                    messageId: $message['id'],
                );

                HandleHoneypotTrigger::dispatch(
                    discordUserId: $message['author']['id'],
                    discordUsername: $message['author']['username'],
                    messageId: $message['id'],
                );
            } else {
                // This is my message - cache it for stats
                Cache::put('discord:honeypot:bot_message', [
                    'channel_id' => $honeypotChannelId,
                    'message_id' => $message['id'],
                ], null);
            }
        }
    }

    /**
     * Cache a message mapping for easy bulk lookup and removal.
     *
     * Stores a mapping of discordUserId -> [{channelId, messageId}] in Redis,
     * deduplicated by messageId, retaining only messages from the last 10 minutes.
     */
    private function cacheMessage(string $discordUserId, string $channelId, string $messageId): void
    {
        $key = "discord:user:{$discordUserId}:messages";
        $messages = Cache::get($key, []);
        $cutoff = now()->subMinutes(10);

        // purge entries older than 10 minutes, then add the new one
        $messages = collect($messages)
            ->reject(fn (array $entry) => isset($entry['cached_at']) && $entry['cached_at'] < $cutoff->timestamp)
            ->put($messageId, [
                'channel_id' => $channelId,
                'message_id' => $messageId,
                'cached_at' => now()->timestamp,
            ])
            ->all();

        Cache::put($key, $messages, 600);
    }
}
