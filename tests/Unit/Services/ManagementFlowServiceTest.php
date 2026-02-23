<?php

namespace Tests\Unit\Services;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Email as AccountEmail;
use App\Services\Mship\ManagementFlowService;
use Tests\TestCase;

class ManagementFlowServiceTest extends TestCase
{
    public function test_should_redirect_landing_passthrough(): void
    {
        $service = new class extends ManagementFlowService
        {
            public function __construct() {}
        };

        $this->assertTrue($service->shouldRedirectLanding(true));
        $this->assertFalse($service->shouldRedirectLanding(false));
    }

    public function test_get_add_secondary_email_redirect_result_maps_response(): void
    {
        $service = new class extends ManagementFlowService
        {
            public function __construct() {}

            public function addSecondaryEmailResponse(Account $account, string $email, string $emailConfirmation): array
            {
                return [
                    'route' => 'mship.manage.email.add',
                    'level' => 'error',
                    'message' => 'Invalid',
                ];
            }
        };

        $result = $service->getAddSecondaryEmailRedirectResult(new Account, 'a@b.com', 'a@b.com');

        $this->assertSame('mship.manage.email.add', $result->route);
        $this->assertSame('error', $result->level);
        $this->assertSame('Invalid', $result->message);
    }

    public function test_get_delete_secondary_email_redirect_result_omits_flash_without_message(): void
    {
        $service = new class extends ManagementFlowService
        {
            public function __construct() {}

            public function deleteSecondaryEmailResponse(Account $account, AccountEmail $email): array
            {
                return [
                    'route' => 'mship.manage.dashboard',
                    'level' => 'error',
                ];
            }
        };

        $result = $service->getDeleteSecondaryEmailRedirectResult(new Account, new AccountEmail);

        $this->assertSame('mship.manage.dashboard', $result->route);
        $this->assertNull($result->level);
        $this->assertNull($result->message);
    }

    public function test_get_verify_email_page_result_maps_redirect_and_non_redirect_outcomes(): void
    {
        $redirectingService = new class extends ManagementFlowService
        {
            public function __construct() {}

            public function getVerifyEmailViewResult(string $code, bool $isAuthenticated): array
            {
                return ['redirect' => true, 'level' => 'success', 'message' => 'Verified'];
            }
        };

        $redirectResult = $redirectingService->getVerifyEmailPageResult('token', true);
        $this->assertTrue($redirectResult->redirect);
        $this->assertSame('mship.manage.dashboard', $redirectResult->route);
        $this->assertSame('success', $redirectResult->level);

        $nonRedirectingService = new class extends ManagementFlowService
        {
            public function __construct() {}

            public function getVerifyEmailViewResult(string $code, bool $isAuthenticated): array
            {
                return ['redirect' => false, 'level' => 'error', 'message' => 'Invalid'];
            }
        };

        $nonRedirectResult = $nonRedirectingService->getVerifyEmailPageResult('token', false);
        $this->assertFalse($nonRedirectResult->redirect);
        $this->assertSame('', $nonRedirectResult->route);
        $this->assertSame('error', $nonRedirectResult->level);
        $this->assertSame('Invalid', $nonRedirectResult->message);
    }
}
