<?php

namespace App\Listeners\TeamSpeak;

use App\Libraries\TeamSpeak;
use App\Models\NetworkData\Atc;
use App\Services\TeamSpeak\AtcServerGroupService;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class RemoveAtcServerGroup
{
    public function __construct(private readonly AtcServerGroupService $service) {}

    public function handle(\App\Events\NetworkData\AtcSessionEnded $event): void
    {
        $atcSession = $event->atcSession;

        if (! Atc::isUk()->where('id', $atcSession->id)->exists()) {
            return;
        }

        if (! TeamSpeak::enabled()) {
            return;
        }

        $account = $event->getAccount();

        /** @var Host|null $server */
        $server = null;

        try {
            $server = TeamSpeak::run('vUK Remove ATC Group');
            $this->service->release($account, $server);
        } catch (\Throwable $e) {
            Log::error('Failed to release ATC server group', [
                'account_id' => $account->id,
                'exception' => $e->getMessage(),
            ]);
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
            // Connection may already be closed — nothing to do
        }
    }
}
