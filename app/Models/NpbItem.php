<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpbItem extends Model
{
    use HasFactory;

    protected $table = 'npb_items';

    protected $fillable = [
        'npb_request_id',
        'urutan',
        'kategori',
        'uraian',
        'karakter',
        'coa_id',
        'nominal_utama',
        'nominal_proyeksi',
        'keterangan',
    ];

    protected $casts = [
        'urutan'           => 'integer',
        'nominal_utama'    => 'float',
        'nominal_proyeksi' => 'float',
    ];

    public function npbRequest()
    {
        return $this->belongsTo(NpbRequest::class, 'npb_request_id');
    }

    public function coa()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_id');
    }

    public function settlementItems()
    {
        return $this->hasMany(NpbSettlementItem::class, 'npb_item_id');
    }
}
