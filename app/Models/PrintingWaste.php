<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintingWaste extends Model
{
    protected $table = 'printing_wastes';

    protected $fillable = [
        'printing_id',
        'error_production_id',
        'qty_waste',
        'catatan',
    ];

    protected $casts = [
        'qty_waste' => 'float',
    ];

    public function printing(): BelongsTo
    {
        return $this->belongsTo(Printing::class, 'printing_id');
    }

    public function errorProduction(): BelongsTo
    {
        return $this->belongsTo(ErrorProduction::class, 'error_production_id');
    }
}
