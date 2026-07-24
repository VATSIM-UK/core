<?php

declare(strict_types=1);

namespace App\Console\Commands\Discord;

use App\Jobs\Discord\HandleHoneypotTrigger;
use App\Models\Discord\DiscordTag;
use App\Models\NetworkData\Atc;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\ApplicationCommand;
use Discord\Parts\User\Activity;
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

            $this->registerTagCommand($discord);

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

            $lastEasterEgg = null;

            $updatePresence = function () use ($discord, &$lastEasterEgg) {
                try {
                    $count = Atc::online()->isUK()->count();

                    if ($count > 0) {
                        $label = $count === 1 ? '1 online controller' : "{$count} online controllers";
                        $type = Activity::TYPE_WATCHING;
                    } else {
                        $easterEggs = [
                            ['name' => 'over missed retention checks', 'type' => Activity::TYPE_WATCHING],
                            ['name' => 'Roblox flight sim', 'type' => Activity::TYPE_PLAYING],
                            ['name' => 'the honeypot for spam victims', 'type' => Activity::TYPE_WATCHING],
                            ['name' => 'EGLL_ATIS', 'type' => Activity::TYPE_LISTENING],
                            ['name' => 'TeamSpeak: Poking idle controllers', 'type' => Activity::TYPE_PLAYING],
                            ['name' => 'the 3-hour roster slackers being removed', 'type' => Activity::TYPE_WATCHING],
                            ['name' => 'the fourth availability failure. Goodbye.', 'type' => Activity::TYPE_WATCHING],
                            ['name' => 'CTS bingo: find the unrostered S1', 'type' => Activity::TYPE_PLAYING],
                            ['name' => 'moss grow on expired waiting list places', 'type' => Activity::TYPE_WATCHING],
                            ['name' => 'the dead being pruned at 3:30 AM', 'type' => Activity::TYPE_WATCHING],
                        ];

                        do {
                            $egg = $easterEggs[array_rand($easterEggs)];
                        } while ($egg === $lastEasterEgg && count($easterEggs) > 1);

                        $lastEasterEgg = $egg;
                        $label = $egg['name'];
                        $type = $egg['type'];
                    }

                    $activity = new Activity($discord, [
                        'name' => $label,
                        'type' => $type,
                    ]);

                    $discord->updatePresence($activity);
                } catch (\Throwable $e) {
                    report($e);
                }
            };

            $updatePresence();

            $discord->getLoop()->addPeriodicTimer(120, $updatePresence);
        });

        $discord->run();
    }

    private function registerTagCommand(Discord $discord): void
    {
        $discord->listenCommand('tag', function (ApplicationCommand $interaction) {
            $key = $interaction->data->options->get('name', 'key')?->value;

            if (! $key) {
                $interaction->respondWithMessage(
                    MessageBuilder::new()->setContent('Please specify a tag key.')
                );

                return;
            }

            $userId = $interaction->member->user->id;
            $channelId = $interaction->channel_id;

            // Per-user rate limit: once per minute
            $userKey = "discord:ratelimit:user:{$userId}";
            $userCooldown = Cache::get($userKey);
            if ($userCooldown) {
                $remaining = 60 - (now()->timestamp - $userCooldown);
                $interaction->respondWithMessage(
                    MessageBuilder::new()->setContent("You're using tags too fast. Try again in {$remaining} seconds."),
                    ephemeral: true
                );

                return;
            }

            // Per-tag-per-channel rate limit: once every 5 minutes
            $tagKey = "discord:ratelimit:tag:{$key}:{$channelId}";
            $tagCooldown = Cache::get($tagKey);
            if ($tagCooldown) {
                $interaction->respondWithMessage(
                    MessageBuilder::new()->setContent("The `{$key}` tag was recently used in this channel. Try again in a few minutes."),
                    ephemeral: true
                );

                return;
            }

            $tag = DiscordTag::where('key', $key)->first();

            if (! $tag) {
                $interaction->respondWithMessage(
                    MessageBuilder::new()->setContent("Tag `{$key}` not found."),
                    ephemeral: true
                );

                return;
            }

            Cache::put($userKey, now()->timestamp, 60);
            Cache::put($tagKey, now()->timestamp, 300);

            $interaction->respondWithMessage(
                MessageBuilder::new()
                    ->addEmbed([
                        'title' => "{$tag->title}",
                        'description' => $tag->value,
                        'color' => 2469347,
                        'footer' => [
                            'text' => "/tag {$tag->key}",
                        ],
                    ])
            );
        });
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
