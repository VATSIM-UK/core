<?php

namespace App\Services\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Services\VisitTransfer\DTO\ApplicationActionResult;
use App\Services\VisitTransfer\DTO\ApplicationContinueRedirectData;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class ApplicationFlowService
{
    public function shouldAutoStartApplication(string $trainingTeam): bool
    {
        return $trainingTeam === 'pilot';
    }

    public function shouldRedirectToLanding(string $route): bool
    {
        return $route === 'visiting.landing';
    }

    public function getCurrentOpenApplicationForUser(?Authenticatable $account): Application
    {
        if (! $account instanceof Account) {
            return new Application;
        }

        return $account->visit_transfer_current ?? new Application;
    }

    public function startApplication(Account $account, string $type, string $team): Application
    {
        return $account->createVisitingTransferApplication([
            'type' => $type,
            'training_team' => $team,
        ]);
    }

    public function startApplicationAction(Account $account, string $type, string $team, string $errorRoute, array $errorRouteParameters = []): ApplicationActionResult
    {
        try {
            $application = $this->startApplication($account, $type, $team);
        } catch (Exception $exception) {
            return new ApplicationActionResult(false, $errorRoute, $errorRouteParameters, 'error', $exception->getMessage());
        }

        return new ApplicationActionResult(
            false,
            'visiting.application.facility',
            [$application->public_id],
            'success',
            'Application started! Please complete all sections to submit your application.'
        );
    }

    public function getContinueRoute(Application $application): string
    {
        if (Gate::allows('select-facility', $application)) {
            return 'visiting.application.facility';
        }

        if (Gate::allows('add-statement', $application) && $application->statement == null) {
            return 'visiting.application.statement';
        }

        if (Gate::allows('submit-application', $application)) {
            return 'visiting.application.submit';
        }

        if (Gate::allows('view', $application)) {
            return 'visiting.application.view';
        }

        return 'visiting.landing';
    }

    public function getContinueRedirectData(Application $application): ApplicationContinueRedirectData
    {
        $route = $this->getContinueRoute($application);

        if ($this->shouldRedirectToLanding($route)) {
            return new ApplicationContinueRedirectData($route);
        }

        return new ApplicationContinueRedirectData($route, [$application->public_id]);
    }

    public function setManualFacility(Application $application, string $facilityCode): void
    {
        $facility = Facility::findByPublicID($facilityCode);
        if (! $facility) {
            throw new Exception('That facility code is invalid.');
        }

        $application->setFacility($facility);
    }

    public function setFacilityById(Application $application, int $facilityId): void
    {
        $application->setFacility(Facility::find($facilityId));
    }

    public function setStatement(Application $application, string $statement): void
    {
        $application->setStatement($statement);
    }

    public function setManualFacilityAction(Application $application, string $facilityCode): ApplicationActionResult
    {
        try {
            $this->setManualFacility($application, $facilityCode);
        } catch (Exception $exception) {
            return new ApplicationActionResult(false, 'visiting.application.facility', [$application->public_id], 'error', $exception->getMessage());
        }

        return new ApplicationActionResult(false, 'visiting.application.continue', [$application->public_id], 'success', 'Facility selection saved!');
    }

    public function setFacilityAction(Application $application, int $facilityId): ApplicationActionResult
    {
        try {
            $this->setFacilityById($application, $facilityId);
        } catch (Exception $exception) {
            return new ApplicationActionResult(false, 'visiting.application.facility', [$application->public_id], 'error', $exception->getMessage());
        }

        return new ApplicationActionResult(false, 'visiting.application.continue', [$application->public_id], 'success', 'Facility selection saved!');
    }

    public function setStatementAction(Application $application, string $statement): ApplicationActionResult
    {
        try {
            $this->setStatement($application, $statement);
        } catch (Exception $exception) {
            return new ApplicationActionResult(false, 'visiting.application.statement', [$application->public_id], 'error', $exception->getMessage());
        }

        return new ApplicationActionResult(false, 'visiting.application.continue', [$application->public_id], 'success', 'Statement completed');
    }

    public function submitAction(Application $application): ApplicationActionResult
    {
        try {
            $this->submit($application);
        } catch (Exception $exception) {
            return new ApplicationActionResult(false, 'visiting.application.submit', [$application->public_id], 'error', $exception->getMessage());
        }

        return new ApplicationActionResult(false, 'visiting.application.view', [$application->public_id], 'success', 'Your application has been submitted! You will be notified when staff have reviewed the details.');
    }

    public function withdrawAction(Application $application): ApplicationActionResult
    {
        try {
            $this->withdraw($application);
        } catch (Exception $exception) {
            return new ApplicationActionResult(false, 'visiting.application.withdraw', [$application->public_id], 'error', $exception->getMessage());
        }

        return new ApplicationActionResult(false, 'visiting.landing', [], 'success', 'Your application has been withdrawn! You can submit a new application as required.');
    }

    public function submit(Application $application): void
    {
        $application->submit();
    }

    public function withdraw(Application $application): void
    {
        $application->withdraw();
    }
}
