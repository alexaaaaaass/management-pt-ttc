<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MasterFinishing extends Model
{
    protected $table = 'master_finishing';

    protected $fillable = [
        'kode_finishing',
        'spk_id',
        'mesin_finishing_id',
        'operator_finishing_id',
        'tanggal_entri',
        'proses_finishing',
        'proses_list',
        'tahap_finishing',
        'status_laporan',
        'hasil_baik',
        'hasil_rusak',
        'semi_waste',
        'error_production_id',
        'keterangan_spk',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_entri' => 'date',
        'proses_list'   => 'array',
    ];

    protected $attributes = [
        'tahap_finishing' => 'reguler',
        'status_laporan'  => 'selesai',
        'keterangan_spk'  => 'reguler',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function mesinFinishing()
    {
        return $this->belongsTo(MesinFinis::class, 'mesin_finishing_id');
    }

    public function operatorFinishing()
    {
        return $this->belongsTo(OperatorFinishing::class, 'operator_finishing_id');
    }

    public function errorProduction()
    {
        return $this->belongsTo(ErrorProduction::class);
    }

    public function wastes()
    {
        return $this->hasMany(FinishingWaste::class, 'master_finishing_id');
    }

    public function getProsesLabelsAttribute(): array
    {
        if (is_array($this->proses_list) && ! empty($this->proses_list)) {
            return array_values(array_filter($this->proses_list));
        }

        if (! empty($this->proses_finishing)) {
            $map = [
                'protol' => 'Protol (Stripping)',
                'sorter' => 'Sorter / QC Cek Visual',
                'lem'    => 'Lem / Folder Gluer',
            ];

            return [$map[$this->proses_finishing] ?? ucfirst((string) $this->proses_finishing)];
        }

        return [];
    }

    public function getProsesSummaryAttribute(): string
    {
        $labels = $this->proses_labels;

        return ! empty($labels) ? implode(', ', $labels) : '-';
    }

    public function syncWasteSummary(): void
    {
        $this->load('wastes');
        $firstWaste = $this->wastes->first();
        $totalWaste = (float) $this->wastes->sum('qty_waste');

        $updates = [
            'error_production_id' => $firstWaste?->error_production_id,
        ];

        if (is_array($this->proses_list) && ! empty($this->proses_list)) {
            $updates['proses_finishing'] = Str::limit(implode(', ', $this->proses_list), 190, '');
        }

        if (empty($this->tahap_finishing)) {
            $updates['tahap_finishing'] = 'reguler';
        }

        if ($this->wastes->isNotEmpty() && $totalWaste > 0) {
            $updates['hasil_rusak'] = (int) round($totalWaste);
        }

        $this->updateQuietly($updates);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO GENERATE KODE FINISHING
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_finishing)) {
                $tanggal = now()->format('Ymd');
                $tahap = strtoupper((string) ($model->tahap_finishing ?: 'REG'));
                $count = static::whereDate('created_at', today())->count() + 1;

                $model->kode_finishing = 'FIN-' . $tahap . '-' . $tanggal . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
