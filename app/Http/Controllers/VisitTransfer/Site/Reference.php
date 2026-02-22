<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Http\Controllers\BaseController;
use App\Http\Requests\VisitTransfer\ReferenceSubmitRequest;
use App\Models\Sys\Token;
use App\Services\VisitTransfer\ReferenceFlowService;
use Exception;
use Illuminate\Support\Facades\Request;
use Redirect;

class Reference extends BaseController
{
    public function __construct(private ReferenceFlowService $referenceFlowService)
    {
        parent::__construct();
    }

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
            $this->referenceFlowService->completeReference($token, (string) Request::input('reference'));
        } catch (Exception $e) {
            return Redirect::route('visiting.reference.complete', [$token->code])->withError($e->getMessage());
        }

        return Redirect::route('visiting.landing')->withSuccess('You have successfully completed a reference for '.$reference->application->account->name.'.  Thank you.');
    }

    public function postCancel(Token $token)
    {
        $reference = $token->related;

        $this->referenceFlowService->cancelReference($token);

        return Redirect::route('visiting.landing')->withSuccess('You have canceled your reference for '.$reference->application->account->name.'.  Thank you.');
    }
}
