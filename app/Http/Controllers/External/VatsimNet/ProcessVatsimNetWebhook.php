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
        $this->validateAuth($request);

        $webhook = $request->all();

        // Sort the actions by timestamp to make sure we process them in order.
        $actions = collect($webhook['actions'] ?? [])->sortBy('timestamp');

        Log::info('Raw webhook data', [
            'webhook' => $webhook,
        ]);

        Log::debug('VATSIM.net webhook received', [
            'resource' => $webhook['resource'] ?? null,
            'actions.length' => $actions->count(),
        ]);

        $jobs = $actions
            ->map(fn (array $action): ShouldQueue => $this->createActionJob((int) $webhook['resource'], $action))
            ->all();

        // Dispatch the jobs in a chain to avoid race conditions between actions.
        Bus::chain($jobs)->dispatch();

        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Resolve a webhook action payload into the queue job that handles it.
     */
    private function createActionJob(int $memberId, array $action): ShouldQueue
    {
        $jobClass = self::ACTION_JOB_MAP[$action['action'] ?? ''] ?? null;

        if (! $jobClass) {
            Log::error("Unknown action from VATSIM.net webook: {$action['action']}");
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
