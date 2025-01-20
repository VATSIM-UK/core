<?php

namespace App\Http\Controllers\External\VatsimNet;

use App\Http\Controllers\BaseController;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberChangedAction;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberCreatedAction;
use Illuminate\Support\Facades\Log;

class ProcessVatsimNetWebhook extends BaseController
{
    public function __invoke()
    {
        if (request()->header('Authorization') !== config('services.vatsim-net.webhook.key')) {
            return response()->json([
                'status' => 'forbidden',
            ], 403);
        }

        foreach (request()->json('actions') as $action) {
            $class = match ($action['action']) {
                'member_created_action' => MemberCreatedAction::class,
                'member_changed_action' => MemberChangedAction::class,
                default => null,
            };

            if (! $class) {
                Log::error("Unhandled webhook from VATSIM.net: {$action['action']}");

                continue;
            }

            dispatch(new $class(request()->json('resource'), $action));
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
