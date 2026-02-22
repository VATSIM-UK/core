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
        $result = $this->processWebhookService->handleWebhook(
            $request->header('Authorization'),
            $request->all()
        );

        return response()->json($result->payload, $result->statusCode);
    }
}
