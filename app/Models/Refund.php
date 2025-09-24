<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Refund extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'QRIS_TRANSACTION_AQUERIER_MAIN';
    /**
     * The attributes that are mass assignable.
     *	
     * @var array
     */
    protected $fillable = [
    ];
}