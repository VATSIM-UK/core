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
        string $messageContent,
    ): void {
        $account = Account::where('discord_id', $discordUserId)->first();

        if (! $account) {
            Log::warning("Honeypot message from unlinked Discord user {$discordUsername} ({$discordUserId}), skipping");

            return;
        }

        // Soft-ban (timeout + bulk-delete recent messages) inside the guild
        $this->discord->softBan($account, 1, 7, 'Honeypot');

        Log::notice("Honeypot triggered by {$discordUsername} ({$discordUserId}): {$messageContent}");

        $noteType = NoteType::isShortCode('discipline')->first();
        $account->addNote($noteType, "User sent a message in the honeypot channel. Message content: {$messageContent}", null);
    }
}
