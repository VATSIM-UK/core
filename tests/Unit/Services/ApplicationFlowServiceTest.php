<?php

namespace Tests\Unit\Services;

use App\Exceptions\Mship\InvalidCIDException;
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

    public function test_map_referee_add_exception_flags_home_region_error_as_back_redirect(): void
    {
        $service = new ApplicationFlowService;

        $regionError = $service->mapRefereeAddException(new Exception('Your referee must be in your home region.'));
        $genericError = $service->mapRefereeAddException(new Exception('Something else'));

        $this->assertTrue($regionError->useBackRedirect);
        $this->assertFalse($genericError->useBackRedirect);
        $this->assertSame('Something else', $genericError->message);
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
        $application->public_id = 'abc123';

        $result = $service->getContinueRedirectData($application);

        $this->assertSame('visiting.application.submit', $result->route);
        $this->assertSame(['abc123'], $result->routeParameters);
    }

    public function test_add_referee_action_handles_invalid_cid_and_back_redirect_error_paths(): void
    {
        $invalidCidService = new class extends ApplicationFlowService
        {
            public function addReferee(Application $application, Account $actor, string $refereeCid, ?string $refereeEmail, ?string $refereeRelationship): array
            {
                throw new InvalidCIDException;
            }
        };

        $application = new Application;
        $application->public_id = 'vt-1';

        $invalidResult = $invalidCidService->addRefereeAction($application, new Account, '123', null, null);
        $this->assertTrue($invalidResult->useBackRedirect);
        $this->assertSame('error', $invalidResult->level);
        $this->assertTrue($invalidResult->withInput);

        $regionService = new class extends ApplicationFlowService
        {
            public function addReferee(Application $application, Account $actor, string $refereeCid, ?string $refereeEmail, ?string $refereeRelationship): array
            {
                throw new Exception('Your referee must be in your home region.');
            }
        };

        $regionResult = $regionService->addRefereeAction($application, new Account, '123', null, null);
        $this->assertTrue($regionResult->useBackRedirect);
        $this->assertSame('Your referee must be in your home region.', $regionResult->message);
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
