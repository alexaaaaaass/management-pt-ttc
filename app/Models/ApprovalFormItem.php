<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalFormItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_form_id',
        'finish_good_item_id',
        'item_name',
        'jenis',
        'komponen',
        'isi',
        'kertas',
        'warna',
        'finishing',
        'qty',
        'satuan',
        'cek_desain',
        'cek_plate',
        'cek_produksi',
        'cek_qc',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'cek_desain' => 'boolean',
        'cek_plate' => 'boolean',
        'cek_produksi' => 'boolean',
        'cek_qc' => 'boolean',
    ];

    public function approvalForm(): BelongsTo
    {
        return $this->belongsTo(ApprovalForm::class);
    }

    public function finishGoodItem(): BelongsTo
    {
        return $this->belongsTo(FinishGoodItem::class);
    }
}
