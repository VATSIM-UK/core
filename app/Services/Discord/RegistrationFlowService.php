<?php

namespace App\Services\Discord;

use App\Events\Discord\DiscordLinked;
use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserInviteException;
use App\Models\Mship\Account;
use App\Services\Discord\DTO\DiscordCodeExchangeResult;
use App\Services\Discord\DTO\DiscordLinkResult;
use App\Services\Discord\DTO\DiscordRegistrationResult;
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

    public function exchangeCode(string $code): DiscordCodeExchangeResult
    {
        try {
            $token = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
            $discordUser = $this->provider->getResourceOwner($token);
        } catch (Exception) {
            return DiscordCodeExchangeResult::failure('Something went wrong. Please try again.');
        }

        if (! strstr($token->getValues()['scope'], 'identify') || ! strstr($token->getValues()['scope'], 'guilds.join')) {
            return DiscordCodeExchangeResult::failure("We didn't get all of the permissions required, please try again.");
        }

        if (Account::where('discord_id', $discordUser->getId())->get()->isNotEmpty()) {
            return DiscordCodeExchangeResult::failure('This Discord account is already linked to a VATSIM UK account. Please contact Web Services.');
        }

        return DiscordCodeExchangeResult::success($token, $discordUser);
    }

    public function linkAccount(Account $account, mixed $discordUser, mixed $token): DiscordLinkResult
    {
        try {
            event(new DiscordLinked($account, $discordUser, $token));
        } catch (DiscordUserInviteException $e) {
            return DiscordLinkResult::failure($e->getMessage());
        }

        return DiscordLinkResult::success();
    }

    public function registerByCode(Account $account, string $code): DiscordRegistrationResult
    {
        $exchangeResult = $this->exchangeCode($code);
        if (! $exchangeResult->ok) {
            return DiscordRegistrationResult::failure((string) ($exchangeResult->message ?? 'Something went wrong. Please try again.'));
        }

        $linkResult = $this->linkAccount(
            $account,
            $exchangeResult->discordUser,
            $exchangeResult->token
        );

        if (! $linkResult->ok) {
            return DiscordRegistrationResult::failure((string) ($linkResult->message ?? 'Something went wrong. Please try again.'));
        }

        return DiscordRegistrationResult::success();
    }

    public function unlinkAccount(Account $account): void
    {
        event(new DiscordUnlinked($account));
    }
}
