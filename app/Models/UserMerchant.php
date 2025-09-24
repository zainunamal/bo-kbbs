<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class UserMerchant extends Model
{
    protected $table = 'user_has_merchant';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
        'USER_ID',
        'MERCHANT_ID',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'MERCHANT_ID');
    }

    public function filterByUser($userId)
    {
        return $this->where('USER_ID', $userId)->with('merchant')->get();
    }

    public function filterByMerchant($merchantId)
    {
        return $this->where('merchant_id', $merchantId)->get();
    }
}