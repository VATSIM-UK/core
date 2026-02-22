<?php

namespace App\Services\VisitTransfer;

use App\Exceptions\Mship\InvalidCIDException;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Models\VisitTransfer\Reference;
use App\Services\VisitTransfer\DTO\ApplicationActionResult;
use App\Services\VisitTransfer\DTO\ApplicationContinueRedirectData;
use App\Services\VisitTransfer\DTO\RefereeAddExceptionData;
use Illuminate\Contracts\Auth\Authenticatable;
use ErrorException;
use Exception;
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

    public function mapRefereeAddException(Exception $exception): RefereeAddExceptionData
    {
        if ($exception->getMessage() === 'Your referee must be in your home region.') {
            return new RefereeAddExceptionData('Your referee must be in your home region.', true);
        }

        return new RefereeAddExceptionData($exception->getMessage(), false);
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

        if (Gate::allows('add-referee', $application) && $application->number_references_required_relative > 0) {
            return 'visiting.application.referees';
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

    /**
     * @return array{redirectRoute: string}
     */
    public function addReferee(Application $application, Account $actor, string $refereeCid, ?string $refereeEmail, ?string $refereeRelationship): array
    {
        $referee = Account::findOrRetrieve($refereeCid);

        try {
            if ($referee->primary_permanent_state->pivot->region != $actor->primary_permanent_state->pivot->region) {
                throw new Exception('Your referee must be in your home region.');
            }
        } catch (ErrorException) {
            // ignore missing region data
        }

        $application->addReferee($referee, $refereeEmail, $refereeRelationship);

        return [
            'redirectRoute' => $application->fresh()->number_references_required_relative == 0
                ? 'visiting.application.submit'
                : 'visiting.application.referees',
        ];
    }

    public function addRefereeAction(
        Application $application,
        Account $actor,
        string $refereeCid,
        ?string $refereeEmail,
        ?string $refereeRelationship
    ): ApplicationActionResult {
        try {
            $flowResult = $this->addReferee($application, $actor, $refereeCid, $refereeEmail, $refereeRelationship);
        } catch (InvalidCIDException) {
            return new ApplicationActionResult(true, '', [], 'error', "There doesn't seem to be a VATSIM user with that ID.", true);
        } catch (Exception $exception) {
            $error = $this->mapRefereeAddException($exception);

            if ($error->useBackRedirect) {
                return new ApplicationActionResult(true, '', [], 'error', $error->message, true);
            }

            return new ApplicationActionResult(false, 'visiting.application.referees', [$application->public_id], 'error', $error->message);
        }

        return new ApplicationActionResult(
            false,
            (string) $flowResult['redirectRoute'],
            [$application->public_id],
            'success',
            sprintf('Referee %s added successfully! They will not be contacted until you submit your application.', $refereeCid)
        );
    }

    public function deleteReferee(Reference $reference): void
    {
        $reference->delete();
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
