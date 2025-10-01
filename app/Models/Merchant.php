<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    // protected $connection = 'mysql2';
    protected $table = 'QRIS_MERCHANT';
    protected $primaryKey = 'ID';
    // protected $table = 'qris_merchant_2';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ID',
        'CREATED_AT',
        'UPDATED_AT',
        'TERMINAL_LABEL',
        'MERCHANT_COUNTRY',
        'QRIS_MERCHANT_DOMESTIC_ID',
        'TYPE_QR',
        'MERCHANT_NAME',
        'MERCHANT_CITY',
        'POSTAL_CODE',
        'MERCHANT_CURRENCY_CODE',
        'MERCHANT_TYPE',
        'MERCHANT_EXP',
        'MERCHANT_CODE',
        'MERCHANT_ADDRESS',
        'STATUS',
        'NMID',
        'ACCOUNT_NUMBER',
        'KTP',
        'NPWP',
        'USER_ID_MOBILE',
        'PHONE_MOBILE',
        'EMAIL_MOBILE',
        'QR_TYPE',
        'MERCHANT_TYPE_2'
    ];


    public function details() {
        return $this->hasOne(MerchantDetails::class, 'MERCHANT_ID', 'ID');
    }

    public function domestic() {
        return $this->hasOne(MerchantDomestic::class, 'ID', 'QRIS_MERCHANT_DOMESTIC_ID');
    }
}
