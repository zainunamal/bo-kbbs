<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class KabKota extends Model
{
    protected $table = 'QRIS_KAB_KOTA'; // Sesuaikan dengan nama tabel yang sebenarnya
    protected $primaryKey = 'ID';  // Pastikan ini adalah kunci utama di tabel
    public $timestamps = false;  // Tidak menggunakan timestamps jika tidak diperlukan

    protected $fillable = [
        'ID', 'KOTA_KABUPATEN', 'KOTA_KABUPATEN_MAX_15' , 'KECAMATAN', 'KODEPOS'
    ];
}