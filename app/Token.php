<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @property string $access_token
 * @property int $resource_owner_id
 * @property string $refresh_token
 * @property int $expires_in
 */
class Token extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'resource_owner_id';
    protected $fillable = [
        'access_token',
        'resource_owner_id',
        'refresh_token',
        'expires_in',
    ];

    public function oauthToken()
    {
        return new AccessToken($this->getAttributes());
    }

    public function renew(array $newValues)
    {
        $this->access_token = $newValues['access_token'];
        $this->refresh_token = $newValues['refresh_token'];
        $this->expires_in = $newValues['expires_in'];
        $this->save();
    }
}
