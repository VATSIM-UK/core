<?php

namespace App\Services\Discord;

use App\Libraries\Discord;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type as NoteType;
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

        Log::notice("Honeypot triggered by {$discordUsername} ({$discordUserId}) linked to account {$account->id}");

        $this->discord->softBan($account, 1, 7, 'Honeypot');

        $noteType = NoteType::isShortCode('discipline')->first();
        $account->addNote($noteType, 'User sent a message in the honeypot channel, recent messages have been deleted and user has been timed out for 7 days', null);
    }
}
