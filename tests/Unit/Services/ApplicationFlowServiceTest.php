<?php

namespace Tests\Unit\Services;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Services\VisitTransfer\ApplicationFlowService;
use Exception;
use Tests\TestCase;

class ApplicationFlowServiceTest extends TestCase
{
    public function test_basic_route_decision_helpers(): void
    {
        $service = new ApplicationFlowService;

        $this->assertTrue($service->shouldAutoStartApplication('pilot'));
        $this->assertFalse($service->shouldAutoStartApplication('atc'));
        $this->assertTrue($service->shouldRedirectToLanding('visiting.landing'));
        $this->assertFalse($service->shouldRedirectToLanding('visiting.application.view'));
    }

    public function test_start_application_action_returns_error_result_when_start_throws(): void
    {
        $service = new class extends ApplicationFlowService
        {
            public function startApplication(Account $account, string $type, string $team): Application
            {
                throw new Exception('Unable to start');
            }
        };

        $result = $service->startApplicationAction(new Account, 'new', 'pilot', 'visiting.landing');

        $this->assertSame('error', $result->level);
        $this->assertSame('visiting.landing', $result->route);
        $this->assertSame('Unable to start', $result->message);
    }

    public function test_get_continue_redirect_data_uses_parameters_for_non_landing_routes(): void
    {
        $service = new class extends ApplicationFlowService
        {
            public function getContinueRoute(Application $application): string
            {
                return 'visiting.application.submit';
            }
        };

        $application = new Application;
        $application->id = 12345;

        $result = $service->getContinueRedirectData($application);

        $this->assertSame('visiting.application.submit', $result->route);
        $this->assertSame([$application->public_id], $result->routeParameters);
    }

    public function test_submit_and_withdraw_action_map_success_and_error_routes(): void
    {
        $service = new class extends ApplicationFlowService
        {
            public function submit(Application $application): void
            {
                throw new Exception('Cannot submit');
            }

            public function withdraw(Application $application): void
            {
                throw new Exception('Cannot withdraw');
            }
        };

        $application = new Application;
        $application->public_id = 'vt-2';

        $submitResult = $service->submitAction($application);
        $withdrawResult = $service->withdrawAction($application);

        $this->assertSame('visiting.application.submit', $submitResult->route);
        $this->assertSame('Cannot submit', $submitResult->message);

        $this->assertSame('visiting.application.withdraw', $withdrawResult->route);
        $this->assertSame('Cannot withdraw', $withdrawResult->message);
    }
}
