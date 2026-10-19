<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderConfirmationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_confirmation_id',
        'approval_form_item_id',
        'finish_good_item_id',
        'item_name',
        'jenis',
        'komponen',
        'isi',
        'kertas',
        'warna',
        'finishing',
        'qty',
        'harga',
        'total',
        'satuan',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function orderConfirmation(): BelongsTo
    {
        return $this->belongsTo(OrderConfirmation::class);
    }

    public function approvalFormItem(): BelongsTo
    {
        return $this->belongsTo(ApprovalFormItem::class);
    }

    public function finishGoodItem(): BelongsTo
    {
        return $this->belongsTo(FinishGoodItem::class);
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->total = (float) $item->qty * (float) $item->harga;
        });

        static::saved(function ($item) {
            $item->orderConfirmation?->recalculateTotals();
        });

        static::deleted(function ($item) {
            $item->orderConfirmation?->recalculateTotals();
        });
    }
}
