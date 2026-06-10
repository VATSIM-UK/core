<?php

namespace App\Console\Commands\TeamSpeak;

use App\Libraries\TeamSpeak;
use App\Services\TeamSpeak\AtcServerGroupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class SyncAtcServerGroups extends Command
{
    protected $signature = 'teamspeak:sync-atc-groups';

    protected $description = 'Sync ATC groups.';

    public function __construct(private readonly AtcServerGroupService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! TeamSpeak::enabled()) {
            $this->info('TeamSpeak is not configured.');

            return self::SUCCESS;
        }

        $server = null;

        try {
            $server = TeamSpeak::run('vUK ATC Groups Sync');
            $this->service->sync($server);
            $this->info('ATC groups synced.');
            Log::info('ATC groups synced.');
        } catch (\Throwable $e) {
            $this->error('Cleanup failed: '.$e->getMessage());
            Log::error('SyncAtcServerGroups failed', ['exception' => $e->getMessage()]);

            return self::FAILURE;
        } finally {
            self::closeConnection($server);
        }

        return self::SUCCESS;
    }

    private static function closeConnection(?Server $server): void
    {
        if ($server === null) {
            return;
        }

        try {
            $server->request('quit');
        } catch (\Throwable) {

        }
    }
}
