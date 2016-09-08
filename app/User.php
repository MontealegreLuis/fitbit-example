<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;
    protected $fillable = [
        'name', 'fitbit_id',
    ];

    public function token()
    {
        return $this->hasOne(Token::class, 'resource_owner_id', 'fitbit_id');
    }
}
