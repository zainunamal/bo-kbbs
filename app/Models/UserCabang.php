<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class UserCabang extends Model
{
    protected $table = 'user_has_cabang';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'USER_ID',
        'KODE_CABANG',
        'KODE_LOKASI',
    ];
}