<?php

namespace App\Http\Controllers\External\VatsimNet;

use App\Http\Controllers\BaseController;
use App\Services\External\VatsimNet\Webhooks\ProcessWebhookService;
use Illuminate\Http\Request;

class ProcessVatsimNetWebhook extends BaseController
{
    public function __construct(private ProcessWebhookService $processWebhookService)
    {
        parent::__construct();
    }

    public function __invoke(Request $request)
    {
        if (! $this->processWebhookService->isAuthorized($request->header('Authorization'))) {
            abort(403);
        }

        $result = $this->processWebhookService->process($request->all());

        if (! $result->isOk()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown action in webhook payload.',
                'action' => $result->message,
            ], 400);
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
