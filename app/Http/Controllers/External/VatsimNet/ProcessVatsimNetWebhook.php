<?php

namespace App\Http\Controllers\External\VatsimNet;

use App\Http\Controllers\BaseController;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberChangedAction;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberCreatedAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcessVatsimNetWebhook extends BaseController
{
    public function __invoke(Request $request)
    {
        $this->validateAuth($request);
        $webhook = $request->all();

        Log::info('Raw webhook data', [
            'webhook' => $webhook,
        ]);

        // Ignore bad payloads
        if (! isset($webhook['resource']) || ! is_array($webhook['actions'] ?? null)) {
            return response()->json([
                'status' => 'ok',
            ]);
        }

        Log::debug('VATSIM.net webhook received', [
            'resource' => $webhook['resource'],
            'actions.length' => count($webhook['actions']),
        ]);

        // Sort the actions by timestamp to make sure that we process them in the correct order
        $actions = $webhook['actions'];
        usort($actions, function ($a, $b) {
            return ($a['timestamp'] ?? null) <=> ($b['timestamp'] ?? null);
        });

        $jobs = [];

        foreach ($actions as $action) {
            if (! is_array($action) || ! isset($action['action'])) {
                continue;
            }

            switch ($action['action']) {
                case 'member_created_action':
                    $jobs[] = new MemberCreatedAction($webhook['resource'], $action);
                    break;
                case 'member_changed_action':
                    $jobs[] = new MemberChangedAction($webhook['resource'], $action);
                    break;
                default:
                    Log::error('Unknown action from VATSIM.net webhook', ['action' => $action['action']]);
                    abort(400);
            }
        }

        // Dispatch the jobs in a chain to ensure that we don't get any race conditions
        \Bus::chain($jobs)->dispatch();

        return response()->json([
            'status' => 'ok',
        ]);
    }

    private function validateAuth(Request $request)
    {
        if ($request->header('Authorization') !== config('services.vatsim-net.webhook.key')) {
            abort(403);
        }
    }
}
