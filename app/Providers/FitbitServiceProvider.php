<?php
/**
 * PHP version 7.0
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */
namespace App\Providers;

use djchen\OAuth2\Client\Provider\Fitbit;
use Illuminate\Support\ServiceProvider;

class FitbitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Fitbit::class, function () {
            return new Fitbit([
                'clientId' => env('FITBIT_KEY'),
                'clientSecret' => env('FITBIT_SECRET'),
                'redirectUri' => env('FITBIT_REDIRECT_URI'),
            ]);
        });
    }
}
