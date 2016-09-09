<?php
/**
 * PHP version 7.0
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */
namespace App;

use Datetime;
use djchen\OAuth2\Client\Provider\Fitbit;

class FitbitClient
{
    /** @var Fitbit */
    private $provider;

    public function __construct(Fitbit $provider)
    {
        $this->provider = $provider;
    }

    public function getActivitiesOn(Datetime $aDate, User $user)
    {
        $request = $this->provider->getAuthenticatedRequest(
            'GET',
            sprintf(
                '%s/1/user/%s/activities/date/%s.json',
                Fitbit::BASE_FITBIT_API_URL,
                $user->fitbit_id,
                $aDate->format('Y-m-d')
            ),
            $this->getTokenFor($user)
        );
        return $this->provider->getResponse($request);
    }

    private function getTokenFor(User $user)
    {
        $token = $user->token;
        $accessToken = $user->token->oauthToken();

        if ($accessToken->hasExpired()) {
            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken()
            ]);

            $token->renew($newAccessToken->getValues());
            $accessToken = $newAccessToken;
        }

        return $accessToken;
    }
}
