<?php

declare(strict_types=1);

namespace App\Console\Commands\Discord;

use App\Libraries\Discord;
use Illuminate\Console\Command;

class SyncDiscordTags extends Command
{
    protected $signature = 'discord:sync-tags';

    protected $description = 'Manually sync all Discord tags to the /tag slash command';

    public function handle(Discord $discord): int
    {
        $this->info('Syncing Discord tags...');

        $discord->syncTagCommands();

        $this->info('Done.');

        return Command::SUCCESS;
    }
}
