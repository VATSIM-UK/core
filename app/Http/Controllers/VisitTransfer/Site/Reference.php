<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransfer\ReferenceSubmitRequest;
use App\Models\Sys\Token;
use App\Models\VisitTransfer\Reference as VisitTransferReference;
use App\Notifications\ApplicationReferenceCancelledByReferee;
use Exception;
use Illuminate\Support\Facades\Request;
use Redirect;

class Reference extends BaseController
{
    public function getComplete(Token $token)
    {
        $reference = $this->resolveReference($token);
        $this->ensureValidReferenceToken($token);

        $this->authorize('complete', $reference);

        $this->setTitle('Complete Reference');

        return $this->viewMake('visit-transfer.site.reference.complete')
            ->with('token', $token)
            ->with('reference', $reference)
            ->with('application', $reference->application);
    }

    public function postComplete(ReferenceSubmitRequest $request, Token $token)
    {
        $reference = $this->resolveReference($token);

        try {
            $reference->submit(Request::input('reference'));
            $token->consume();
        } catch (Exception $e) {
            return Redirect::route('visiting.reference.complete', [$token->code])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('You have successfully completed a reference for '.$reference->application->account->name.'.  Thank you.');
    }

    public function postCancel(Token $token)
    {
        $reference = $this->resolveReference($token);
        $this->ensureValidReferenceToken($token);

        $this->authorize('complete', $reference);

        $reference->status_note = 'Referee reported that applicant is not known to them';
        $reference->cancel();

        $reference->application->account->notify(new ApplicationReferenceCancelledByReferee($reference));
        $reference->application->markAsUnderReview();

        return Redirect::route('visiting.landing')->withSuccess('You have canceled your reference for '.$reference->application->account->name.'.  Thank you.');
    }

    private function resolveReference(Token $token): VisitTransferReference
    {
        if (! $token->related instanceof VisitTransferReference) {
            abort(404);
        }

        return $token->related;
    }

    private function ensureValidReferenceToken(Token $token): void
    {
        if (
            $token->type !== 'visittransfer_reference_request'
            || $token->used_at !== null
            || $token->is_expired
        ) {
            abort(403);
        }
    }
}
