<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Wilayah extends Model
{
    protected $table = 'QRIS_WILAYAH'; // Sesuaikan dengan nama tabel yang sebenarnya
    protected $primaryKey = 'ID';  // Pastikan ini adalah kunci utama di tabel
    public $timestamps = false;  // Tidak menggunakan timestamps jika tidak diperlukan

    protected $fillable = [
        'ID', 'PROVINSI', 'DAERAH_TINGKAT'
    ];
}