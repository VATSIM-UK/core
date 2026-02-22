<?php

namespace App\Services\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Models\VisitTransfer\Reference;
use ErrorException;
use Exception;
use Illuminate\Support\Facades\Gate;

class ApplicationFlowService
{
    public function startApplication(Account $account, string $type, string $team): Application
    {
        return $account->createVisitingTransferApplication([
            'type' => $type,
            'training_team' => $team,
        ]);
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

    public function deleteReferee(Reference $reference): void
    {
        $reference->delete();
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
