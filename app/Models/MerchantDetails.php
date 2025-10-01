<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantDetails extends Model
{
    protected $table = 'QRIS_MERCHANT_DETAILS';
    protected $primaryKey = 'ID';
    public $timestamps = false;
       
    /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'ID',
        'MERCHANT_ID',
        'DOMAIN',
        'TAG',
        'MPAN',
        'MID',
        'CRITERIA'
    ];

    public function merchant() {
        return $this->belongsTo(Merchant::class, 'MERCHANT_ID', 'ID');
    }

    public function criteria()
    {
        return $this->belongsTo(Criteria::class, 'CRITERIA', 'ID');
    }


}
