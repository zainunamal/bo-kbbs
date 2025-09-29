<?php

namespace App;

use App\Models\Cabang;
use App\Models\Merchant;
use App\Models\UserCabang;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function merchants()
    {
        return $this->belongsToMany(
            Merchant::class,
            'user_has_merchant',
            'USER_ID',
            'MERCHANT_ID'
        );
    }

    public function cabangs()
    {
        return $this->belongsToMany(
            Cabang::class,
            'user_has_cabang',
            'USER_ID',
            'KODE_CABANG',
            'id',
            'CPC_MC_KODE_CABANG'
        )
            ->withPivot('KODE_LOKASI')
            ->whereColumn('user_has_cabang.KODE_LOKASI', 'cpccore_master_cabang.CPC_MC_KODE_LOKASI');
    }

}
