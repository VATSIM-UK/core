<?php

namespace App\Http\Controllers\Discord;

use App\Events\Discord\DiscordLinked;
use App\Events\Discord\DiscordUnlinked;
use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use Illuminate\Http\Request;
use Wohali\OAuth2\Client\Provider\Discord;
use Exception;

class Registration extends BaseController
{
    /**
     * @var Discord
     */
    private $provider;

    public function show()
    {
        return $this->viewMake('discord.new');
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
            return $this->error('Something went wrong. Please try again.');
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

        if (Account::where('discord_id', $discordUser->getId())->get()->isNotEmpty()) {
            return $this->error('This Discord account is already linked to a VATSIM UK account. Please contact Web Services.');
        }

        event(new DiscordLinked($request->user(), $discordUser->getId()));

        return redirect()->route('mship.manage.dashboard')->withSuccess('Your Discord account has been linked and you will be able to access our Discord server shortly.');
    }

    public function destroy(Request $request)
    {
        event(new DiscordUnlinked($request->user()));

        return redirect()->back()->withSuccess('Your Discord account is being removed. This should take less than 15 minutes.');
    }

    protected function error(string $message)
    {
        return redirect()->route('discord.show')->withError($message);
    }

    protected function initProvider()
    {
        $this->provider = new Discord([
            'clientId'     => config('services.discord.client_id'),
            'clientSecret' => config('services.discord.client_secret'),
            'redirectUri'  => config('services.discord.redirect_uri'),
        ]);
    }
}
