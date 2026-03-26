<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FinishGoodItemMaterial;

class FinishGoodItem extends Model
{
    protected $fillable = [
        'customer_id',
        'type_item_id',
        'satuan_id',
        'kode_material_produk',
        'kode_barcode',
        'pc_number',
        'nama_barang',
        'deskripsi',
        'spesifikasi_kertas',
        'up_satu',
        'up_dua',
        'up_tiga',
        'ukuran_potong',
        'ukuran_cetak',
        'panjang',
        'lebar',
        'tinggi',
        'berat_kotor',
        'berat_bersih',
    ];

    // Relasi
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function typeItem()
    {
        return $this->belongsTo(TypeItem::class);
    }

    public function satuan()
    {
        return $this->belongsTo(MasterSatuan::class);
    }

    public function materials()
    {
        return $this->hasMany(FinishGoodItemMaterial::class);
    }
    protected static function booted()
{
    static::deleting(function ($model) {
        $model->materials()->delete(); // 🔥 hapus BOM dulu
    });
}
}