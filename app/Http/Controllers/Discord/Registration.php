<?php

namespace App\Http\Controllers\Discord;

use App\Events\Discord\DiscordLinked;
use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserInviteException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Mship\Account\DiscordRegistration;
use App\Models\Mship\Account;
use Exception;
use Illuminate\Http\Request;
use Wohali\OAuth2\Client\Provider\Discord;

class Registration extends BaseController
{
    public function __construct(private Discord $provider)
    {
        parent::__construct();
    }

    public function show()
    {
        return redirect()->route('mship.manage.dashboard');
    }

    public function create(Request $request)
    {
        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => ['identify', 'guilds.join'],
        ]);

        return redirect()->away($authUrl);
    }

    public function store(DiscordRegistration $request)
    {
        $inputs = $request->validated();

        try {
            $token = $this->provider->getAccessToken('authorization_code', ['code' => $inputs['code']]);
            $discordUser = $this->provider->getResourceOwner($token);
        } catch (Exception $e) {
            return $this->error('Something went wrong. Please try again.');
        }

        if (! strstr($token->getValues()['scope'], 'identify') || ! strstr($token->getValues()['scope'], 'guilds.join')) {
            return $this->error("We didn't get all of the permissions required, please try again.");
        }

        if (Account::where('discord_id', $discordUser->getId())->get()->isNotEmpty()) {
            return $this->error('This Discord account is already linked to a VATSIM UK account. Please contact Web Services.');
        }

        try {
            event(new DiscordLinked($request->user(), $discordUser, $token));
        } catch (DiscordUserInviteException $e) {
            return $this->error($e->getMessage());
        }

        return redirect()->route('mship.manage.dashboard')->withSuccess('Your Discord account has been linked and you will be able to access our Discord server shortly, go to Discord to see!');
    }

    public function destroy(Request $request)
    {
        event(new DiscordUnlinked($request->user()));

        return redirect()->back()->withSuccess('Your Discord account is being removed. This should take less than 15 minutes.');
    }

    protected function error(string $message)
    {
        return redirect()->route('mship.manage.dashboard')->withError($message);
    }
}
