<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DieMaking extends Model
{
    protected $table = 'diemakings';

    protected $fillable = [
        'spk_id',
        'mesin_id',
        'operator_id',
        'tanggal_entri',
        'proses_diemaking',
        'proses_list',
        'tahap_diemaking',
        'status_laporan',
        'hasil_baik',
        'hasil_rusak',
        'semi_waste',
        'error_production_id',
        'note_waste',
        'keterangan_spk',
        'catatan',
    ];

    protected $casts = [
        'tanggal_entri' => 'date',
        'proses_list'   => 'array',
    ];

    protected $attributes = [
        'tahap_diemaking' => 'Laporan Akhir',
        'status_laporan'  => 'selesai',
        'keterangan_spk'  => 'Reguler',
    ];

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function mesin()
    {
        return $this->belongsTo(MesinDieMaking::class, 'mesin_id');
    }

    public function operator()
    {
        return $this->belongsTo(OperatorDiemaking::class, 'operator_id');
    }

    public function errorProduction()
    {
        return $this->belongsTo(ErrorProduction::class, 'error_production_id');
    }

    public function wastes()
    {
        return $this->hasMany(DieMakingWaste::class, 'diemaking_id');
    }

    public function getProsesLabelsAttribute(): array
    {
        if (is_array($this->proses_list) && ! empty($this->proses_list)) {
            return array_values(array_filter($this->proses_list));
        }

        return ! empty($this->proses_diemaking) ? [$this->proses_diemaking] : [];
    }

    public function getProsesSummaryAttribute(): string
    {
        $labels = $this->proses_labels;

        return ! empty($labels) ? implode(', ', $labels) : '-';
    }

    public function syncWasteSummary(): void
    {
        $this->load('wastes.errorProduction');
        $firstWaste = $this->wastes->first();
        $totalWaste = (float) $this->wastes->sum('qty_waste');

        $updates = [
            'error_production_id' => $firstWaste?->error_production_id,
            'note_waste'          => $firstWaste?->errorProduction?->nama_error,
        ];

        if (is_array($this->proses_list) && ! empty($this->proses_list)) {
            $updates['proses_diemaking'] = Str::limit(implode(', ', $this->proses_list), 190, '');
        }

        if (empty($this->tahap_diemaking)) {
            $updates['tahap_diemaking'] = 'Laporan Akhir';
        }

        if ($this->wastes->isNotEmpty() && $totalWaste > 0) {
            $updates['hasil_rusak'] = (int) round($totalWaste);
        }

        $this->updateQuietly($updates);
    }
}
