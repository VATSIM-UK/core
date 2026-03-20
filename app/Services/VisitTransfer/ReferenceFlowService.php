<?php

namespace App\Services\VisitTransfer;

use App\Models\Sys\Token;
use App\Notifications\ApplicationReferenceCancelledByReferee;

class ReferenceFlowService
{
    public function completeReference(Token $token, string $referenceText): void
    {
        $reference = $token->related;
        $reference->submit($referenceText);
        $token->consume();
    }

    public function cancelReference(Token $token): void
    {
        $reference = $token->related;
        $reference->status_note = 'Referee reported that applicant is not known to them';
        $reference->cancel();

        $reference->application->account->notify(new ApplicationReferenceCancelledByReferee($reference));
        $reference->application->markAsUnderReview();
    }
}
