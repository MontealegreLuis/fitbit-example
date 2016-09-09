<?php
/**
 * PHP version 7.0
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */
namespace App\Http\Controllers;

use App\FitbitClient;
use DateTime;
use Illuminate\Http\Request;

class FitbitController extends Controller
{
    /** @var FitbitClient */
    private $fitbit;

    public function __construct(FitbitClient $fitbit)
    {
        $this->fitbit = $fitbit;
    }

    public function activities(Request $request)
    {
        $response = $this->fitbit->getActivitiesOn(
            new DateTime(), $request->user()
        );

        return $response;
    }
}
