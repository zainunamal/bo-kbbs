<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of Health
 *
 * @author User
 */
class Health extends Model
{

    protected $table = 'HEALTHY_MONITORING_SERVICE_LIST';
    protected $primaryKey = 'ID';
    // protected $table = 'qris_merchant_2';
    public $timestamps = false;

    public function getActiveDescAttribute($value)
    {
        $str = preg_replace('/\r\n|\r|\n/', ' ', $value);
        return htmlentities($str);
    }

}
