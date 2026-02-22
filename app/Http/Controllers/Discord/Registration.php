<?php

namespace App\Http\Controllers\Discord;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Mship\Account\DiscordRegistration;
use App\Services\Discord\RegistrationFlowService;
use Illuminate\Http\Request;

class Registration extends BaseController
{
    public function __construct(private RegistrationFlowService $registrationFlowService)
    {
        parent::__construct();
    }

    public function show()
    {
        return redirect()->route('mship.manage.dashboard');
    }

    public function create(Request $request)
    {
        return redirect()->away($this->registrationFlowService->getAuthorizationUrl());
    }

    public function store(DiscordRegistration $request)
    {
        $result = $this->registrationFlowService->registerByCode($request->user(), (string) $request->validated('code'));

        if (! $result->ok) {
            return $this->error((string) $result->message);
        }

        return redirect()->route('mship.manage.dashboard')->withSuccess('Your Discord account has been linked and you will be able to access our Discord server shortly, go to Discord to see!');
    }

    public function destroy(Request $request)
    {
        $this->registrationFlowService->unlinkAccount($request->user());

        return redirect()->back()->withSuccess('Your Discord account is being removed. This should take less than 15 minutes.');
    }

    protected function error(string $message)
    {
        return redirect()->route('mship.manage.dashboard')->withError($message);
    }
}
