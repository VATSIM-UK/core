<?php

namespace App\Http\Controllers\External\VatsimNet;

use App\Http\Controllers\BaseController;

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
            $class = config("services.vatsim-net.webhook.jobs.{$action['action']}");
            if ($class && class_exists($class)) {
                dispatch(new $class(request()->json('resource'), $action))->afterResponse();
            }
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
