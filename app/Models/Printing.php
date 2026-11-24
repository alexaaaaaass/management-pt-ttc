<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ErrorProduction;
class Printing extends Model
{
    protected $table = 'printings';

  protected $fillable = [
    'spk_id',
    'mesin_id',
    'operator_id',
    'tanggal_entri',
    'proses_printing',
    'proses_list',
    'tahap_printing',
    'status_laporan',
    'satuan_hasil',
    'hasil_baik',
    'hasil_rusak',
    'semi_waste',
    'error_production_id',
    'keterangan_spk',
];

    protected $casts = [
        'tanggal_entri' => 'date',
        'proses_list'   => 'array',
    ];

    protected $attributes = [
        'tahap_printing' => 'proses_cetak',
        'status_laporan' => 'selesai',
        'satuan_hasil'   => 'm',
    ];

    public function getSatuanUnitAttribute(): string
    {
        return MasterProsesProduksi::satuanSuffix($this->satuan_hasil, 'm');
    }

    public function spk()
    {
        return $this->belongsTo(MasterSpk::class, 'spk_id');
    }

    public function mesin()
    {
        return $this->belongsTo(MasterPrinting::class, 'mesin_id');
    }

    public function operator()
    {
        return $this->belongsTo(MasterOperator::class, 'operator_id');
    }
    

    public function errorProduction()
    {
        return $this->belongsTo(ErrorProduction::class);
    }

    public function wastes()
    {
        return $this->hasMany(PrintingWaste::class, 'printing_id');
    }

    public function getProsesLabelsAttribute(): array
    {
        if (is_array($this->proses_list) && ! empty($this->proses_list)) {
            return array_values(array_filter($this->proses_list));
        }

        if (! empty($this->proses_printing)) {
            $map = [
                'color-printing' => 'Color Printing',
                'cold-foil'      => 'Cold Foil',
                'spot-uv'        => 'Spot UV',
                'furnish-uv'     => 'Furnish / Varnish UV',
                'drip-off'       => 'Drip Off',
            ];

            return [$map[$this->proses_printing] ?? $this->proses_printing];
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
            $updates['proses_printing'] = \Illuminate\Support\Str::limit(implode(', ', $this->proses_list), 190, '');
        }

        if (empty($this->tahap_printing)) {
            $updates['tahap_printing'] = 'proses_cetak';
        }

        if ($this->wastes->isNotEmpty() && $totalWaste > 0) {
            $updates['hasil_rusak'] = (int) round($totalWaste);
        }

        $this->updateQuietly($updates);
    }
}