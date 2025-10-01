<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantDomestic extends Model
{
    protected $table = 'QRIS_MERCHANT_DOMESTIC';
    protected $primaryKey = 'ID';
    public $timestamps = false;
       
    /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'ID',
        'REVERSE_DOMAIN',
        'NMID',
        'MCC',
        'CRITERIA'
    ];

}
