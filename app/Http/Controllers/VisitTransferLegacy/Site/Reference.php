<?php

namespace App\Http\Controllers\VisitTransferLegacy\Site;

use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransferLegacy\ReferenceSubmitRequest;
use App\Models\Sys\Token;
use App\Notifications\ApplicationReferenceCancelledByReferee;
use Exception;
use Illuminate\Support\Facades\Request;
use Redirect;

class Reference extends BaseController
{
    public function getComplete(Token $token)
    {
        $reference = $token->related;

        $this->authorize('complete', $reference);

        $this->setTitle('Complete Reference');

        return $this->viewMake('visit-transfer.site.reference.complete')
            ->with('token', $token)
            ->with('reference', $reference)
            ->with('application', $reference->application);
    }

    public function postComplete(ReferenceSubmitRequest $request, Token $token)
    {
        $reference = $token->related;

        try {
            $reference->submit(Request::input('reference'));
            $token->consume();
        } catch (Exception $e) {
            dd($e);

            return Redirect::route('visiting.reference.complete', [$token->code])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('You have successfully completed a reference for '.$reference->application->account->name.'.  Thank you.');
    }

    public function postCancel(Token $token)
    {
        $reference = $token->related;
        $reference->status_note = 'Referee reported that applicant is not known to them';
        $reference->cancel();

        $reference->application->account->notify(new ApplicationReferenceCancelledByReferee($reference));
        $reference->application->markAsUnderReview();

        return Redirect::route('visiting.landing')->withSuccess('You have canceled your reference for '.$reference->application->account->name.'.  Thank you.');
    }
}
