<?php

namespace App\Services\Discord;

use App\Events\Discord\DiscordLinked;
use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserInviteException;
use App\Models\Mship\Account;
use Exception;
use Wohali\OAuth2\Client\Provider\Discord;

class RegistrationFlowService
{
    public function __construct(private Discord $provider) {}

    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl([
            'scope' => ['identify', 'guilds.join'],
        ]);
    }

    /**
     * @return array{ok: bool, message?: string, token?: mixed, discordUser?: mixed}
     */
    public function exchangeCode(string $code): array
    {
        try {
            $token = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
            $discordUser = $this->provider->getResourceOwner($token);
        } catch (Exception) {
            return ['ok' => false, 'message' => 'Something went wrong. Please try again.'];
        }

        if (! strstr($token->getValues()['scope'], 'identify') || ! strstr($token->getValues()['scope'], 'guilds.join')) {
            return ['ok' => false, 'message' => "We didn't get all of the permissions required, please try again."];
        }

        if (Account::where('discord_id', $discordUser->getId())->get()->isNotEmpty()) {
            return ['ok' => false, 'message' => 'This Discord account is already linked to a VATSIM UK account. Please contact Web Services.'];
        }

        return ['ok' => true, 'token' => $token, 'discordUser' => $discordUser];
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    public function linkAccount(Account $account, mixed $discordUser, mixed $token): array
    {
        try {
            event(new DiscordLinked($account, $discordUser, $token));
        } catch (DiscordUserInviteException $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }

        return ['ok' => true];
    }

    public function unlinkAccount(Account $account): void
    {
        event(new DiscordUnlinked($account));
    }
}
