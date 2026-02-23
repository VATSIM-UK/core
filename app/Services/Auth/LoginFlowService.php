<?php

namespace App\Services\Auth;

use App\Providers\VATSIMOAuthProvider;
use App\Services\Auth\DTO\LoginAttemptResult;
use App\Services\Auth\DTO\LoginAuthorizationData;
use App\Services\Auth\DTO\LoginStateValidationResult;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class LoginFlowService
{
    public function __construct(
        private VATSIMOAuthProvider $provider,
        private VatsimLoginService $vatsimLoginService
    ) {}

    public function shouldStartAuthorizationFlow(bool $hasCode, bool $hasState): bool
    {
        return ! $hasCode || ! $hasState;
    }

    public function loginFailureMessage(string $reason): string
    {
        if ($reason === 'missing_permissions') {
            return 'You cannot use our services unless you provide the relevant permissions upon login. Please try again.';
        }

        return 'Something went wrong, please try again.';
    }

    public function requiresSecondaryLogin(mixed $account): bool
    {
        return $account->hasPassword();
    }

    public function getAuthorizationData(): LoginAuthorizationData
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl([
            'required_scopes' => implode(' ', config('services.vatsim-net.connect.scopes')),
        ]);

        return new LoginAuthorizationData($authorizationUrl, $this->provider->getState());
    }

    public function validateState(string $state, ?string $storedState): LoginStateValidationResult
    {
        if ($state === $storedState) {
            return LoginStateValidationResult::valid();
        }

        return LoginStateValidationResult::invalid('Something went wrong, please try again.');
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
}
