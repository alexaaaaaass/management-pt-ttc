<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PracetakProcess extends Model
{
    protected $table = 'pracetak_processes';

    protected $fillable = [
        'spk_id',
        'approval_form_id',
        'order_confirmation_id',
        'finish_good_item_id',
        'qty',
        'status',
        'ket_spk',
        'is_repeat_order',
        'plate_archive_id',
        'nama_plat',
        'hasil_baik',
        'hasil_rusak',
        'tgl_entry',
        'operator_id',
        'mesin_id',
        // A. Trial Design
        'status_file',
        'status_kesiapan_file',
        'toleransi_hasil',
        'catatan_design',
        // B. Prove Print
        'is_prove_print',
        'bahan_prove_print',
        'status_prove_print',
        'arsip_proses_prove',
        // C. Plate Making
        'jumlah_warna_plate',
        'detail_warna_plate',
        'kerusakan_plate',
        'catatan_kerusakan_plate',
        // D. Penyimpanan Material & Finishing
        'lokasi_simpan_plate',
        'material_pendukung',
        'toleransi_rusak_hilang',
        // Costing & Handover
        'estimasi_biaya_plate',
        'actual_design_cost',
        'actual_prove_cost',
        'actual_material_cost',
        'actual_process_cost',
        'actual_total_cost',
        'cost_variance',
        'material_posted_at',
        'handed_over_at',
        'handed_over_to',
    ];

    protected $casts = [
        'is_prove_print'       => 'boolean',
        'is_repeat_order'      => 'boolean',
        'tgl_entry'            => 'date',
        'estimasi_biaya_plate' => 'decimal:2',
        'actual_design_cost'   => 'decimal:2',
        'actual_prove_cost'    => 'decimal:2',
        'actual_material_cost' => 'decimal:2',
        'actual_process_cost'  => 'decimal:2',
        'actual_total_cost'    => 'decimal:2',
        'cost_variance'        => 'decimal:2',
        'material_posted_at'   => 'datetime',
        'handed_over_at'       => 'datetime',
    ];

    protected $attributes = [
        'status' => 'design',
    ];

    public function spk(): BelongsTo
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function approvalForm(): BelongsTo
    {
        return $this->belongsTo(ApprovalForm::class, 'approval_form_id');
    }

    public function orderConfirmation(): BelongsTo
    {
        return $this->belongsTo(OrderConfirmation::class, 'order_confirmation_id');
    }

    public function finishGoodItem(): BelongsTo
    {
        return $this->belongsTo(FinishGoodItem::class, 'finish_good_item_id');
    }

    public function plateArchive(): BelongsTo
    {
        return $this->belongsTo(PracetakPlateArchive::class, 'plate_archive_id');
    }

    public function generatedArchive(): HasOne
    {
        return $this->hasOne(PracetakPlateArchive::class, 'pracetak_process_id');
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(PracetakMaterialUsage::class, 'pracetak_process_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(OperatorPraCetak::class, 'operator_id');
    }

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(MesinPraCetak::class, 'mesin_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'design', 'trial_design' => 'A. Trial Design',
            'prove_print'            => 'B. Prove Print',
            'plat_making'            => 'C. Plate Making',
            'selesai'                => 'D. Selesai & Tersimpan di Rak',
            'handed_over'            => 'Diserahkan ke Printing',
            default                  => ucfirst((string) $this->status),
        };
    }

    public function getHasUnpostedUsagesAttribute(): bool
    {
        return $this->materialUsages()->where('status', 'draft')->exists();
    }

    public function getTotalSheetTerpakaiAttribute(): float
    {
        return (float) $this->materialUsages()
            ->where('status', 'posted')
            ->sum('qty_sheet_terpakai');
    }
}
