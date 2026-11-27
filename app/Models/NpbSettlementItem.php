<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpbSettlementItem extends Model
{
    use HasFactory;

    protected $table = 'npb_settlement_items';

    public const METODE_OPTIONS = [
        'BANK_TTC'          => 'Transfer Bank PT TTC (BRI / BCA)',
        'KAS_TTC'           => 'Kas / Petty Cash PT TTC',
        'TALANGAN_INDIGAMA' => 'Talangan PT Indigama (Non-Kas → 2144)',
        'BELUM_DIBAYAR'     => 'Belum Dibayar / Ditunda',
    ];

    public const STATUS_ITEM_OPTIONS = [
        'TEREALISASI' => 'Terealisasi / Lunas',
        'SEBAGIAN'    => 'Terealisasi Sebagian',
        'DITUNDA'     => 'Ditunda',
        'DIBATALKAN'  => 'Dibatalkan',
    ];

    protected $fillable = [
        'npb_settlement_id',
        'npb_item_id',
        'urutan',
        'kategori',
        'uraian',
        'karakter',
        'nominal_anggaran',
        'nominal_realisasi',
        'selisih',
        'tanggal_realisasi',
        'metode_pembayaran',
        'coa_beban_id',
        'coa_sumber_id',
        'no_referensi_bukti',
        'lampiran_bukti',
        'status_item',
        'keterangan',
    ];

    protected $casts = [
        'urutan'            => 'integer',
        'nominal_anggaran'  => 'float',
        'nominal_realisasi' => 'float',
        'selisih'           => 'float',
        'tanggal_realisasi' => 'date',
    ];

    public function settlement()
    {
        return $this->belongsTo(NpbSettlement::class, 'npb_settlement_id');
    }

    public function npbItem()
    {
        return $this->belongsTo(NpbItem::class, 'npb_item_id');
    }

    public function coaBeban()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_beban_id');
    }

    public function coaSumber()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_sumber_id');
    }
}
