<?php
/**
 * PHP version 7.0
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */
namespace App\Http\Controllers;

use App\Token;
use App\User;
use Auth;
use djchen\OAuth2\Client\Provider\Fitbit;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class FitbitAuthenticationController extends Controller
{
    /** @var Fitbit */
    private $provider;

    public function __construct(Fitbit $provider)
    {
        $this->provider = $provider;
    }

    public function redirectToProvider(Request $request)
    {
        if (!$request->has('code')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            session()->put('oauth2state', $this->provider->getState());

            return redirect()->to($authorizationUrl);
        }
    }

    public function handleProviderCallback(Request $request)
    {
        if (!$request->has('state') || $request->get('state') !== session('oauth2state')) {
            session()->forget('oauth2state');
            abort(404);
        } else {
            try {
                $accessToken = $this->provider->getAccessToken('authorization_code', [
                    'code' => $request->get('code'),
                ]);

                $resourceOwner = $this->provider->getResourceOwner($accessToken)->toArray();

                /** @var User $owner */
                $owner = User::firstOrCreate([
                    'name' => $resourceOwner['fullName'],
                    'fitbit_id' => $resourceOwner['encodedId'],
                ]);
                $tokenDetails = [
                    'access_token' => $accessToken->getToken(),
                    'resource_owner_id' => $owner->fitbit_id,
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                ];
                if (!$owner->token) {
                    Token::create($tokenDetails);
                } else {
                    $owner->token->renew($tokenDetails);
                }

                Auth::login($owner);

                return redirect()->action('FitbitController@activities');

            } catch (IdentityProviderException $e) {
                abort(404);
            }
        }
    }
}
