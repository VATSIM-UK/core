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
    private const ACTION_JOB_MAP = [
        'member_created_action' => MemberCreatedAction::class,
        'member_changed_action' => MemberChangedAction::class,
    ];

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

        if ($result->status === 'invalid_payload') {
            return new ProcessWebhookHttpResult(400, [
                'status' => 'error',
                'message' => 'Malformed webhook payload.',
                'reason' => $result->message,
            ]);
        }

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
        $resource = $webhook['resource'] ?? null;
        if (! is_numeric($resource) || (int) $resource <= 0) {
            Log::error('VATSIM.net webhook received with invalid or missing resource', [
                'resource' => $resource,
            ]);

            return ProcessWebhookResult::invalidPayload('invalid_resource');
        }

        $rawActions = $webhook['actions'] ?? null;
        if (! is_array($rawActions)) {
            Log::error('Malformed VATSIM.net webhook payload: actions is not an array', [
                'resource' => (int) $resource,
                'actions_type' => gettype($rawActions),
            ]);

            return ProcessWebhookResult::invalidPayload('actions_not_array');
        }

        Log::info('Raw webhook data', [
            'webhook' => $webhook,
        ]);

        Log::debug('VATSIM.net webhook received', [
            'resource' => (int) $resource,
            'actions.length' => count($rawActions),
        ]);

        foreach ($rawActions as $action) {
            if (! is_array($action)) {
                Log::error('Malformed VATSIM.net webhook payload: action item is not an array', [
                    'resource' => (int) $resource,
                    'action' => $action,
                ]);

                return ProcessWebhookResult::invalidPayload('action_not_array');
            }

            if (! array_key_exists('timestamp', $action)) {
                Log::error('Malformed VATSIM.net webhook payload: action missing timestamp', [
                    'resource' => (int) $resource,
                    'action' => $action,
                ]);

                return ProcessWebhookResult::invalidPayload('missing_timestamp');
            }
        }

        $actions = $rawActions;
        usort($actions, fn (array $a, array $b) => $a['timestamp'] <=> $b['timestamp']);

        $jobs = [];

        foreach ($actions as $action) {
            $actionName = $action['action'] ?? null;
            $jobClass = is_string($actionName) ? (self::ACTION_JOB_MAP[$actionName] ?? null) : null;

            if (! $jobClass) {
                Log::error('Unknown action from VATSIM.net webhook', [
                    'resource' => (int) $resource,
                    'action' => $actionName ?? 'missing',
                ]);

                return ProcessWebhookResult::unknownAction((string) ($actionName ?? 'missing'));
            }

            $jobs[] = new $jobClass((int) $resource, $action);
        }

        Bus::chain($jobs)->dispatch();

        return ProcessWebhookResult::ok();
    }

    public function isAuthorized(?string $authorizationHeader): bool
    {
        return $authorizationHeader === config('services.vatsim-net.webhook.key');
    }
}
