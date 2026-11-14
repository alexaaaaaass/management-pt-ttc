<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApprovalForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'form_no',
        'customer_id',
        'tanggal',
        'lead_time',
        'catatan_produksi',
        'file_design',
        'status',
        'disetujui_marketing',
        'disetujui_produksi',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ApprovalFormItem::class);
    }

    public function orderConfirmation(): HasOne
    {
        return $this->hasOne(OrderConfirmation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Cek apakah seluruh item spesifikasi sudah memiliki Master Finish Good yang terdaftar
     */
    public function hasAllFinishGoods(): bool
    {
        $items = $this->items;
        if ($items->isEmpty()) {
            return false;
        }

        return $items->every(function ($item) {
            return ! empty($item->finish_good_item_id) && $item->finishGoodItem !== null;
        });
    }

    /**
     * Cek apakah Order Confirmation sudah pernah diterbitkan untuk Approval Form ini
     */
    public function hasOrderConfirmation(): bool
    {
        return $this->status === 'converted_to_oc'
            || ! is_null($this->orderConfirmation);
    }

    /**
     * Cek status kesiapan Bill of Materials (BOM) di Finish Good
     */
    public function getBomStatusAttribute(): string
    {
        $items = $this->items;
        if ($items->isEmpty()) {
            return 'pending';
        }

        $withBom = 0;
        $total = $items->count();

        foreach ($items as $item) {
            if ($item->finish_good_item_id && $item->finishGoodItem && $item->finishGoodItem->materials()->exists()) {
                $withBom++;
            }
        }

        if ($withBom === $total) {
            return 'complete'; // Semua item punya BOM
        } elseif ($withBom > 0) {
            return 'partial'; // Sebagian item punya BOM
        } else {
            return 'pending'; // Belum ada BOM
        }
    }

    /**
     * Generate Nomor Dokumen AF Otomatis (Sesuai Bismillah MACRO.xlsm: AF-YY-ROMAWI-CustID-UrutanCustomer)
     * Contoh format: AF-26-IV-C005-003
     */
    public static function generateFormNo(?Customer $customer = null): string
    {
        $orderId = self::generateOrderId($customer);
        return 'AF-' . substr($orderId, 3);
    }

    /**
     * Generate Order ID Otomatis (Sesuai Bismillah MACRO.xlsm: OR-YY-ROMAWI-CustID-UrutanCustomer)
     * Contoh format: OR-26-IV-C005-003
     */
    public static function generateOrderId(?Customer $customer = null): string
    {
        $year = date('y');
        $monthRoman = self::toRoman((int) date('n'));
        $custCode = $customer ? ($customer->kode_customer ?: 'C' . str_pad($customer->id, 3, '0', STR_PAD_LEFT)) : 'C001';

        $prefix = "OR-{$year}-{$monthRoman}-{$custCode}-";
        $countForCustomer = $customer
            ? (self::where('customer_id', $customer->id)->count() + 1)
            : (self::where('order_id', 'like', "OR-{$year}-{$monthRoman}-%")->count() + 1);
        $sequence = str_pad($countForCustomer, 3, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    public static function toRoman(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        return $map[$month] ?? 'I';
    }
}
