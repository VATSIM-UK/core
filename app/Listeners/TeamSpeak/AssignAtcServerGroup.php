<?php

namespace App\Listeners\TeamSpeak;

use App\Events\NetworkData\AtcSessionStarted;
use App\Libraries\TeamSpeak;
use App\Models\NetworkData\Atc;
use App\Services\TeamSpeak\AtcServerGroupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class AssignAtcServerGroup implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'teamspeak';

    public int $tries = 3;

    public int $backoff = 5;

    public int $delay = 2;

    public function __construct(private readonly AtcServerGroupService $service) {}

    public function handle(AtcSessionStarted $event): void
    {
        $atcSession = $event->atcSession;

        if (! Atc::isUk()->where('id', $atcSession->id)->exists()) {
            return;
        }

        if (! TeamSpeak::enabled()) {
            return;
        }

        $account = $atcSession->account;
        $callsign = $atcSession->callsign;

        $server = null;

        try {
            $server = TeamSpeak::run('vUK Assign ATC Group');
            $this->service->assign($account, $callsign, $server);
        } catch (\Throwable $e) {
            Log::error('Failed to assign ATC server group', [
                'account_id' => $account->id,
                'callsign' => $callsign,
                'exception' => $e->getMessage(),
            ]);

            $this->fail($e);
        } finally {
            self::closeConnection($server);
        }
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
