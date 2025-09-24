<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mcc extends Model
{
    protected $table = 'ap_qris_mcc';
    public $timestamps = false;
    protected $primaryKey = 'ID';

    protected $fillable = ['CODE_MCC', 'DESC_MCC'];
}
