<?php

namespace App\Providers;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;

class VATSIMOAuthProvider extends GenericProvider
{
    /**
     * @var GenericProvider
     */
    private $provider;

    /**
     * Initializes the provider variable.
     */
    public function __construct()
    {
        parent::__construct([
            'clientId' => config('services.vatsim-net.connect.id'),
            'clientSecret' => config('services.vatsim-net.connect.secret'),
            'redirectUri' => route('login.post'),
            'urlAuthorize' => config('services.vatsim-net.connect.base').'/oauth/authorize',
            'urlAccessToken' => config('services.vatsim-net.connect.base').'/oauth/token',
            'urlResourceOwnerDetails' => config('services.vatsim-net.connect.base').'/api/user',
            'scopes' => config('services.vatsim-net.connect.scopes'),
            'scopeSeparator' => ' ',
        ]);
    }

    /**
     * Gets an (updated) user token.
     *
     * @param  Token  $token
     * @return Token
     */
    public static function updateToken($token)
    {
        $controller = new self;
        try {
            return $controller->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken(),
            ]);
        } catch (IdentityProviderException $e) {
            return null;
        }
    }
}
