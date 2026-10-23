<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderConfirmation extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_form_id',
        'order_id',
        'doc_no',
        'customer_id',
        'tanggal',
        'lead_time',
        'subtotal',
        'diskon',
        'grand_total',
        'terms_conditions',
        'catatan',
        'status',
        'disetujui_customer',
        'diketahui_ttc',
        'sales_order_id',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function approvalForm(): BelongsTo
    {
        return $this->belongsTo(ApprovalForm::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderConfirmationItem::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hitung ulang subtotal dan grand_total
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $grandTotal = max(0, $subtotal - ($this->diskon ?? 0));

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ]);
    }

    /**
     * Generate Nomor Dokumen SC/OC Otomatis
     * Contoh format: SC-26-VII-C006-003
     */
    public static function generateDocNo(?Customer $customer = null): string
    {
        $year = date('y');
        $monthRoman = ApprovalForm::toRoman((int) date('n'));
        $custCode = $customer ? ($customer->kode_customer ?: 'C' . str_pad($customer->id, 3, '0', STR_PAD_LEFT)) : 'C001';
        
        $prefix = "SC-{$year}-{$monthRoman}-{$custCode}-";
        $countThisMonth = self::where('doc_no', 'like', "SC-{$year}-{$monthRoman}-%")->count() + 1;
        $sequence = str_pad($countThisMonth, 3, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    public static function defaultTermsConditions(): string
    {
        return "1. Spesifikasi, quantity, dan harga telah disepakati.\n" .
               "2. Perubahan setelah persetujuan dapat mempengaruhi biaya dan waktu produksi.\n" .
               "3. Produksi dimulai setelah dokumen ini disetujui.";
    }
}
