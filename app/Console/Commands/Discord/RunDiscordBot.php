<?php

namespace App\Console\Commands\Discord;

use App\Libraries\Discord as CoreDiscord;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type as NoteType;
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
    public $coreDiscord;

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

                if ((string) $message->channel_id === (string) config('services.discord.honeypot_channel_id')) {
                    $this->handleHoneypotMessage($message, $discord);
                }
            });
        });

        $discord->run();
    }

    public function handleHoneypotMessage(Message $message, Discord $discord)
    {
        $discordAuthor = $message->author;
        $message->delete();

        $account = Account::where('discord_id', (string) $discordAuthor->id)->first();

        if (! $account) {
            Log::warning("Honeypot message from unlinked Discord user {$discordAuthor->username} ({$discordAuthor->id}), skipping");

            return;
        }

        $this->coreDiscord->softBan($account, 1, 7, 'Honeypot');

        Log::notice("Message received in honeypot channel from {$discordAuthor->username} ({$discordAuthor->id}): {$message->content}");

        $account->addNote(NoteType::isShortCode('discipline')->first(), "User sent a message in the honeypot channel. Message content: {$message->content}", null);
    }
}
