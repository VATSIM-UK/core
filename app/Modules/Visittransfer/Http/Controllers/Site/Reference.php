<?php

namespace App\Modules\Visittransfer\Http\Controllers\Site;

use Input;
use Redirect;
use Exception;
use App\Models\Sys\Token;
use App\Http\Controllers\BaseController;
use App\Modules\Visittransfer\Http\Requests\ReferenceSubmitRequest;

class Reference extends BaseController
{
    public function getComplete(Token $token)
    {
        $reference = $token->related;

        $this->authorize('complete', $reference);

        return $this->viewMake('visittransfer::site.reference.complete')
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
}
