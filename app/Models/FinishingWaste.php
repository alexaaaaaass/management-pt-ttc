<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishingWaste extends Model
{
    protected $table = 'finishing_wastes';

    protected $fillable = [
        'master_finishing_id',
        'error_production_id',
        'qty_waste',
        'catatan',
    ];

    protected $casts = [
        'qty_waste' => 'float',
    ];

    public function finishing(): BelongsTo
    {
        return $this->belongsTo(MasterFinishing::class, 'master_finishing_id');
    }

    public function errorProduction(): BelongsTo
    {
        return $this->belongsTo(ErrorProduction::class, 'error_production_id');
    }
}
