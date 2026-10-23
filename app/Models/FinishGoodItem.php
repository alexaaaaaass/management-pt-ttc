<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FinishGoodItemMaterial;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinishGoodItem extends Model
{
    use SoftDeletes;
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
        'kertas_items',

        // Spesifikasi Produk Cetak & Kemasan
        'jenis',
        'komponen',
        'isi',
        'warna',
        'finishing',

        // tambahan
        'gramasi',
        'tebal_kertas',

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

    protected $casts = [
        'kertas_items' => 'array',
    ];

    /**
     * Accessor: jika kertas_items masih kosong tapi ada spesifikasi_kertas legacy, kembalikan 1 item otomatis.
     */
    public function getKertasItemsAttribute($value)
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (!empty($decoded) && is_array($decoded)) {
            return $decoded;
        }

        if (!empty($this->spesifikasi_kertas) || !empty($this->gramasi) || !empty($this->up_satu)) {
            return [
                [
                    'nama_kertas'    => $this->spesifikasi_kertas,
                    'bagian_kemasan' => $this->komponen ?: 'Utama',
                    'gramasi'        => $this->gramasi,
                    'tebal_kertas'   => $this->tebal_kertas,
                    'up'             => $this->up_satu,
                    'ukuran_cetak'   => $this->ukuran_cetak,
                    'keterangan'     => null,
                ]
            ];
        }

        return [];
    }

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

    public function salesOrders()
    {
        return $this->morphMany(SalesOrder::class, 'itemable');
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            $items = $model->kertas_items;
            if (is_string($items)) {
                $items = json_decode($items, true);
            }

            if (!empty($items) && is_array($items)) {
                $paperNames = [];
                foreach ($items as $item) {
                    $name = trim($item['nama_kertas'] ?? $item['spesifikasi_kertas'] ?? '');
                    if ($name !== '') {
                        $part = !empty($item['bagian_kemasan']) ? " [{$item['bagian_kemasan']}]" : '';
                        $gsm = !empty($item['gramasi']) ? " {$item['gramasi']} GSM" : '';
                        $paperNames[] = $name . $gsm . $part;
                    }
                }

                if (!empty($paperNames)) {
                    $model->spesifikasi_kertas = implode(' + ', $paperNames);
                }

                // Sinkronkan data item pertama ke kolom legacy untuk menjaga kompatibilitas
                $first = $items[0] ?? [];
                if (!empty($first)) {
                    $model->gramasi = $first['gramasi'] ?? $model->gramasi;
                    $model->tebal_kertas = $first['tebal_kertas'] ?? $model->tebal_kertas;
                    $model->up_satu = $first['up'] ?? $first['up_satu'] ?? $model->up_satu;
                    $model->ukuran_cetak = $first['ukuran_cetak'] ?? $model->ukuran_cetak;
                }
            }
        });

        static::deleting(function ($model) {
            $model->materials()->delete(); // 🔥 hapus BOM dulu
        });
    }
}