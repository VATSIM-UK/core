<?php

namespace App\Console\Commands\TeamSpeak;

use App\Libraries\TeamSpeak;
use App\Services\TeamSpeak\AtcServerGroupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class CleanupAtcServerGroups extends Command
{
    protected $signature = 'teamspeak:cleanup-atc-groups';

    protected $description = 'Reconcile ATC session assignments and prune empty groups.';

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

        /** @var Host|null $server */
        $server = null;

        try {
            $server = TeamSpeak::run('vUK ATC Cleanup Bot');
            $this->service->sync($server);
            $this->info('ATC session groups reconciled.');
            Log::info('CleanupAtcServerGroups: reconciled ATC session groups.');
        } catch (\Throwable $e) {
            $this->error('Cleanup failed: '.$e->getMessage());
            Log::error('CleanupAtcServerGroups failed', ['exception' => $e->getMessage()]);

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
            // Connection may already be closed — nothing to do
        }
    }
}
