<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class MerchantSnapCredentials extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;

    use Notifiable;

    // protected $connection = 'mysql2';
    protected $table = 'QRIS_MERCHANT_SNAP_CREDENTIALS';
    protected $primaryKey = 'ID';

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'MERCHANT_ID', 'ID');
    }

    protected $hidden = [
        'SECRET_KEY',
    ];

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return ['CID' => $this->CLIENT_ID];
    }

    public function getAuthPassword() {
        return $this->SECRET_KEY;
    }
}
