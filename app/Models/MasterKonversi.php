<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKonversi extends Model
{
    protected $table = 'master_konversis';

    protected $fillable = [
        'type_item_id',
        'satuan_satu_id',
        'satuan_dua_id',
        'nilai_konversi'
    ];

    public function typeItem()
    {
        return $this->belongsTo(TypeItem::class);
    }

    public function satuanSatu()
    {
        return $this->belongsTo(MasterSatuan::class,'satuan_satu_id');
    }

    public function satuanDua()
    {
        return $this->belongsTo(MasterSatuan::class,'satuan_dua_id');
    }
}