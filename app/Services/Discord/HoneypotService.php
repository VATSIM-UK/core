<?php

namespace App\Services\Discord;

use App\Libraries\Discord;
use App\Models\Discord\HoneypotStat;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type as NoteType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HoneypotService
{
    public function __construct(
        private readonly Discord $discord,
    ) {}

    /**
     * Handle a honeypot trigger - discord soft ban.
     * See the soft ban method for details
     */
    public function handleTrigger(
        string $discordUserId,
        string $discordUsername,
        string $messageId,
    ): void {
        $account = Account::where('discord_id', $discordUserId)->first();

        if (! $account) {
            Log::warning("Honeypot message from unlinked Discord user {$discordUsername} ({$discordUserId}), skipping");

            $this->discord->deleteMessage(
                channelId: config('services.discord.honeypot_channel_id'),
                messageId: $messageId,
            );

            return;
        }

        HoneypotStat::create([
            'account_id' => $account->id,
        ]);

        Log::notice("Honeypot triggered by {$discordUsername} ({$discordUserId}) linked to account {$account->id}");

        $this->discord->softBan($account, 7, 'Honeypot');

        $noteType = NoteType::isShortCode('discipline')->first();
        $account->addNote($noteType, 'User sent a message in the honeypot channel, recent messages have been deleted and user has been timed out for 7 days', null);

        $this->discord->sendMessageToChannel(
            channelId: config('services.discord.moderators_chat_channel_id'),
            messageContents: [
                'content' => "Honeypot triggered by <@{$discordUserId}> linked to account [{$account->id}](https://www.vatsim.uk/admin/accounts/{$account->id})",
            ],
        );

        $honeypotMessageData = Cache::get('discord:honeypot:bot_message');

        if (! $honeypotMessageData) {
            \Log::warning('Cached honeypoot bot message not found');

            return;
        }

        $victimCount = HoneypotStat::count();

        $this->discord->editMessage(
            channelId: $honeypotMessageData['channel_id'],
            messageId: $honeypotMessageData['message_id'],
            newContent: [
                'content' => null,
                'embeds' => [
                    [
                        'title' => 'DO NOT SEND MESSAGES HERE',
                        'description' => '"**If you send a message to this channel you will be muted!**\n\n_This channel is designed as a trap for spammers._',
                        'color' => 2469347,
                        'footer' => [
                            'text' => "So far I've baited {$victimCount} people",
                        ],
                    ],
                ],
            ],
        );
    }
}
