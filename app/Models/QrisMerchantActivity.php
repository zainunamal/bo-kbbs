<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrisMerchantActivity extends Model
{
    // use HasFactory;

    protected $table = 'QRIS_MERCHANT_ACTIVITY';

    protected $fillable = [
        'merchant_id',
        'nmid',

        'user_id',
        'action',
        'timestamp',
        'ip_address',
       
        'comment',
        'activity_type',
        'raw'
    ];

    public $timestamps = true; // Mengaktifkan created_at dan updated_at
}
