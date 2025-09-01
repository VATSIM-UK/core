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

        \Log::debug('VATSIM.net webhook received', [
            'resource' => $webhook['resource'],
            'actions.length' => count($webhook['actions']),
        ]);

        // Sort the actions by timestamp to make sure that we process them in the correct order
        $actions = $webhook['actions'];
        usort($actions, function ($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });

        $jobs = [];

        foreach ($actions as $action) {
            switch ($action['action']) {
                case 'member_created_action':
                    $jobs[] = new MemberCreatedAction($webhook['resource'], $action);
                    break;
                case 'member_changed_action':
                    $jobs[] = new MemberChangedAction($webhook['resource'], $action);
                    break;
                default:
                    Log::error("Unknown action from VATSIM.net webook: {$action['action']}");
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
