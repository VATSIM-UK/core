<?php

namespace App\Services\Auth;

use App\Providers\VATSIMOAuthProvider;
use App\Services\Auth\DTO\LoginAttemptResult;
use App\Services\Auth\DTO\LoginAuthorizationData;
use Illuminate\Support\Facades\Session;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class LoginFlowService
{
    public function __construct(
        private VATSIMOAuthProvider $provider,
        private VatsimLoginService $vatsimLoginService
    ) {}

    public function getAuthorizationData(): LoginAuthorizationData
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl([
            'required_scopes' => implode(' ', config('services.vatsim-net.connect.scopes')),
        ]);

        return new LoginAuthorizationData($authorizationUrl, $this->provider->getState());
    }

    public function isValidState(string $state, ?string $storedState): bool
    {
        return $state === $storedState;
    }

    public function authenticateFromCode(string $code, string $ipAddress): LoginAttemptResult
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (IdentityProviderException) {
            return LoginAttemptResult::failure('identity_provider');
        }

        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        if (! $this->vatsimLoginService->hasRequiredResourceOwnerFields($resourceOwner)) {
            return LoginAttemptResult::failure('missing_permissions');
        }

        return LoginAttemptResult::success(
            $this->vatsimLoginService->completeLogin($resourceOwner, $accessToken, $ipAddress)
        );
    }

    public function pullIntendedUrl(string $fallbackRoute): string
    {
        return Session::pull('url.intended', $fallbackRoute);
    }
}
