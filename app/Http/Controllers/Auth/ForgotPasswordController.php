<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Services\Auth\ForgotPasswordFlowService;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

/**
 * This controller is responsible for handling password reset emails and
 * includes a trait which assists in sending these notifications from
 * your application to your users. Feel free to explore this trait.
 */
class ForgotPasswordController extends BaseController
{
    use SendsPasswordResetEmails;

    public function __construct(private ForgotPasswordFlowService $forgotPasswordFlowService)
    {
        parent::__construct();
    }

    /**
     * Send a reset link to the given user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $response = $this->forgotPasswordFlowService->sendResetLink($this->broker(), (int) Auth::guard('vatsim-sso')->id());

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
