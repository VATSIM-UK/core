<?php

namespace App\Services\External\VatsimNet\Webhooks;

use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberChangedAction;
use App\Jobs\ExternalServices\VatsimNet\Webhooks\MemberCreatedAction;
use App\Services\External\VatsimNet\Webhooks\DTO\ProcessWebhookHttpResult;
use App\Services\External\VatsimNet\Webhooks\DTO\ProcessWebhookResult;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessWebhookService
{
    /**
     * @param  array<string, mixed>  $webhook
     */
    public function handleWebhook(?string $authorizationHeader, array $webhook): ProcessWebhookHttpResult
    {
        if (! $this->isAuthorized($authorizationHeader)) {
            return new ProcessWebhookHttpResult(403, [
                'status' => 'error',
                'message' => 'Forbidden',
            ]);
        }

        $result = $this->process($webhook);

        if (! $result->isOk()) {
            return new ProcessWebhookHttpResult(400, [
                'status' => 'error',
                'message' => 'Unknown action in webhook payload.',
                'action' => $result->message,
            ]);
        }

        return new ProcessWebhookHttpResult(200, [
            'status' => 'ok',
        ]);
    }

    /**
     * @param  array<string, mixed>  $webhook
     */
    public function process(array $webhook): ProcessWebhookResult
    {
        Log::info('Raw webhook data', [
            'webhook' => $webhook,
        ]);

        Log::debug('VATSIM.net webhook received', [
            'resource' => $webhook['resource'],
            'actions.length' => count($webhook['actions']),
        ]);

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

                    return ProcessWebhookResult::unknownAction((string) $action['action']);
            }
        }

        Bus::chain($jobs)->dispatch();

        return ProcessWebhookResult::ok();
    }

    public function isAuthorized(?string $authorizationHeader): bool
    {
        return $authorizationHeader === config('services.vatsim-net.webhook.key');
    }
}
