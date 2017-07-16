<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransfer\ReferenceSubmitRequest;
use App\Models\Sys\Token;
use Exception;
use Input;
use Redirect;

class Reference extends BaseController
{
    public function getComplete(Token $token)
    {
        $reference = $token->related;

        $this->authorize('complete', $reference);

        return $this->viewMake('visit-transfer.site.reference.complete')
                    ->with('token', $token)
                    ->with('reference', $reference)
                    ->with('application', $reference->application);
    }

    public function postComplete(ReferenceSubmitRequest $request, Token $token)
    {
        $reference = $token->related;

        try {
            $reference->submit(Input::get('reference'));
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
        $reference->cancel();

        $reference->application->markAsUnderReview();

        return Redirect::route('visiting.landing')->withSuccess('You have canceled your reference for '.$reference->application->account->name.'.  Thank you.');
    }
}
