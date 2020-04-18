<?php

namespace App\Http\Controllers\Auth;

use League\OAuth2\Client\Token;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class VatsimOAuthController extends GenericProvider
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
            'clientId'                => config('vatsim-connect.id'),
            'clientSecret'            => config('vatsim-connect.secret'),
            'redirectUri'             => route('login'),
            'urlAuthorize'            => config('vatsim-connect.base') . '/oauth/authorize',
            'urlAccessToken'          => config('vatsim-connect.base') . '/oauth/token',
            'urlResourceOwnerDetails' => config('vatsim-connect.base') . '/api/user',
            'scopes'                  => config('vatsim-connect.scopes'),
            'scopeSeparator'          => ' '
        ]);
    }

    /**
     * Gets an (updated) user token
     * @param Token $token
     * @return Token
     * @return null
     */
    public static function updateToken($token)
    {
        $controller = new VatsimOAuthController;
        try {
            return $controller->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken()
            ]);
        } catch (IdentityProviderException $e) {
            return null;
        }
    }
}
