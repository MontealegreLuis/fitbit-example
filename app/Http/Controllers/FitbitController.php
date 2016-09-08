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
use DateTime;
use djchen\OAuth2\Client\Provider\Fitbit;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class FitbitController extends Controller
{
    public function redirectToProvider(Request $request)
    {
        $provider = new Fitbit([
            'clientId' => env('FITBIT_KEY'),
            'clientSecret' => env('FITBIT_SECRET'),
            'redirectUri' => env('FITBIT_REDIRECT_URI'),
        ]);

        if (!$request->has('code')) {
            $authorizationUrl = $provider->getAuthorizationUrl();
            session()->put('oauth2state', $provider->getState());

            return redirect()->to($authorizationUrl);
        }
    }

    public function handleProviderCallback(Request $request)
    {
        $provider = new Fitbit([
            'clientId' => env('FITBIT_KEY'),
            'clientSecret' => env('FITBIT_SECRET'),
            'redirectUri' => env('FITBIT_REDIRECT_URI'),
        ]);

        if (!$request->has('state') || $request->get('state') !== session('oauth2state')) {
            session()->forget('oauth2state');
            abort(404);
        } else {
            try {
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $request->get('code'),
                ]);

                $resourceOwner = $provider->getResourceOwner($accessToken)->toArray();

                $owner = User::firstOrCreate([
                    'name' => $resourceOwner['fullName'],
                    'fitbit_id' => $resourceOwner['encodedId'],
                ]);
                $tokenDetails = [
                    'access_token' => $accessToken->getToken(),
                    'resource_owner_id' => $owner->fitbit_id,
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires_in' => $accessToken->getExpires(),
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

    public function activities()
    {
        $provider = new Fitbit([
            'clientId' => env('FITBIT_KEY'),
            'clientSecret' => env('FITBIT_SECRET'),
            'redirectUri' => env('FITBIT_REDIRECT_URI'),
        ]);

        $token = Auth::user()->token;
        $accessToken = $token->oauthToken();

        if ($accessToken->hasExpired()) {
            $newAccessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken()
            ]);

            $token->renew($newAccessToken->getValues());
            $accessToken = $newAccessToken;
        }

        $now = new DateTime();
        $request = $provider->getAuthenticatedRequest(
            'GET',
            Fitbit::BASE_FITBIT_API_URL . '/1/user/-/activities/date/'. $now->format('Y-m-d') . '.json',
            $accessToken
        );
        $response = $provider->getResponse($request);
        dd($response);
    }
}
