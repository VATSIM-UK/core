<?php

namespace App\Http\Controllers\Discord;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use http\Exception;
use Illuminate\Http\Request;
use Wohali\OAuth2\Client\Provider\Discord;

class Registration extends BaseController
{
    /**
     * @var Discord
     */
    private $provider;

    protected function initProvider()
    {
        $this->provider = new Discord([
            'clientId'     => config('services.discord.client_id'),
            'clientSecret' => config('services.discord.client_secret'),
            'redirectUri'  => config('services.discord.redirect_uri'),
        ]);
    }

    public function create(Request $request)
    {
        $this->initProvider();

        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => ['identify']
        ]);

        return redirect()->away($authUrl);
    }

    public function store(Request $request)
    {
        $this->initProvider();

        if (empty($request->input('code'))) {
            return redirect()->route('mship.manage.dashboard');
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', ['code' => $request->input('code')]);
            $discordUser = $this->provider->getResourceOwner($token);
        } catch (Exception $e) {
            return $this->error('Something went wrong. Please try again.');
        }

        if (! strstr($token->getValues()['scope'], 'identify')) {
            return $this->error("We didn't get all of the permissions required, please try again.");
        }

        if (Account::where('discord_id', $discordUser->getId())) {
            return $this->error('This Discord account is already linked to a VATSIM UK account. Please contact Web Services.');
        }

        $user = auth()->user();
        $user->discord_id = $discordUser->getId();
        $user->save();

        return redirect()->route('mship.manage.dashboard')->withSuccess('Your Discord account has been linked.');
    }

    protected function error(string $message)
    {
        return redirect()->route('mship.manage.dashboard')->withError($message);
    }
}
