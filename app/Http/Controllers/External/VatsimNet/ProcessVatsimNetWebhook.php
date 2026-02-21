<?php

namespace App\Http\Controllers\External\VatsimNet;

use App\Http\Controllers\BaseController;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberChangedAction;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberCreatedAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessVatsimNetWebhook extends BaseController
{
    private const ACTION_JOB_MAP = [
        'member_created_action' => MemberCreatedAction::class,
        'member_changed_action' => MemberChangedAction::class,
    ];

    /**
     * Validate, normalise and dispatch VATSIM.net webhook actions in timestamp order.
     */
    public function __invoke(Request $request)
    {
        $startedAt = microtime(true);
        $this->validateAuth($request);

        $webhook = $request->all();
        $memberId = (int) ($webhook['resource'] ?? 0);

        // Sort the actions by timestamp to make sure we process them in order.
        $actions = collect($webhook['actions'] ?? [])->sortBy('timestamp')->values();

        Log::info('VATSIM.net webhook received', [
            'resource' => $memberId,
            'actions.length' => $actions->count(),
            'actions.types' => $actions->pluck('action')->values(),
        ]);

        if ($actions->isEmpty()) {
            Log::warning('VATSIM.net webhook received with no actions', [
                'resource' => $memberId,
            ]);

            return response()->json([
                'status' => 'ok',
            ]);
        }

        $jobs = $actions
            ->map(fn (array $action): ShouldQueue => $this->createActionJob($memberId, $action))
            ->all();

        // Dispatch the jobs in a chain to avoid race conditions between actions.
        Bus::chain($jobs)->dispatch();

        Log::debug('VATSIM.net webhook jobs chained', [
            'resource' => $memberId,
            'jobs.length' => count($jobs),
            'preparation_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Resolve a webhook action payload into the queue job that handles it.
     */
    private function createActionJob(int $memberId, array $action): ShouldQueue
    {
        $actionName = $action['action'] ?? 'missing';
        $jobClass = self::ACTION_JOB_MAP[$actionName] ?? null;

        if (! $jobClass) {
            Log::error('Unknown action from VATSIM.net webhook', [
                'resource' => $memberId,
                'action' => $actionName,
            ]);
            abort(400);
        }

        return new $jobClass($memberId, $action);
    }

    /**
     * Ensure webhook requests are authorised with the configured shared secret.
     */
    private function validateAuth(Request $request): void
    {
        if ($request->header('Authorization') !== config('services.vatsim-net.webhook.key')) {
            abort(403);
        }
    }
}
