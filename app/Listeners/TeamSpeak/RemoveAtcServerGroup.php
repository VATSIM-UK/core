<?php

namespace App\Listeners\TeamSpeak;

use App\Events\NetworkData\AtcSessionEnded;
use App\Libraries\TeamSpeak;
use App\Models\NetworkData\Atc;
use App\Services\TeamSpeak\AtcServerGroupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RemoveAtcServerGroup implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'teamspeak';

    public int $tries = 3;

    public int $backoff = 5;

    public int $delay = 5;

    public function __construct(private readonly AtcServerGroupService $service) {}

    public function handle(AtcSessionEnded $event): void
    {
        $atcSession = $event->atcSession;

        if (! Atc::isUk()->where('id', $atcSession->id)->exists()) {
            return;
        }

        if (! TeamSpeak::enabled()) {
            return;
        }

        $account = $event->getAccount();

        $server = null;

        try {
            $server = TeamSpeak::run('vUK Remove ATC Group');
            $this->service->releaseExisting($account, $server);
        } catch (\Throwable $e) {
            Log::error('Failed to release ATC server group', [
                'account_id' => $account->id,
                'exception' => $e->getMessage(),
            ]);

            $this->fail($e);
        } finally {
            TeamSpeak::close($server);
        }
    }
}
