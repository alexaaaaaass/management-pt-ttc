<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DieMakingWaste extends Model
{
    protected $table = 'diemaking_wastes';

    protected $fillable = [
        'diemaking_id',
        'error_production_id',
        'qty_waste',
        'catatan',
    ];

    protected $casts = [
        'qty_waste' => 'float',
    ];

    public function dieMaking(): BelongsTo
    {
        return $this->belongsTo(DieMaking::class, 'diemaking_id');
    }

    public function errorProduction(): BelongsTo
    {
        return $this->belongsTo(ErrorProduction::class, 'error_production_id');
    }
}
