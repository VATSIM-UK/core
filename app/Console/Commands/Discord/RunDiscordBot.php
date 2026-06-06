<?php

namespace App\Console\Commands\Discord;

use App\Libraries\Discord as CoreDiscord;
use App\Models\Mship\Account;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
     * @var CoreDiscord
     */
    protected $coreDiscord;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->coreDiscord = app()->make(CoreDiscord::class);

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

                if ($message->channel_id == config('services.discord.honeypot_channel_id')) {
                    $discordAuthor = $message->author;
                    $message->delete();

                    $account = Account::where('discord_id', $discordAuthor->id)->first();

                    $this->coreDiscord->softBan($account, 1, 7, 'Honeypot');

                    Log::notice("Message received in honeypot channel from {$message->author->username} ({$message->author->id}): {$message->content}");

                    $account->notes()->create([
                        'admin_id' => null,
                        'content' => "User sent a message in the honeypot channel. Message content: {$message->content}",
                    ]);
                }
            });
        });

        $discord->run();
    }
}
