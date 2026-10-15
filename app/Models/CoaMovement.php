<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoaMovement extends Model
{
    use HasFactory;

    protected $table = 'coa_movements';

    protected $fillable = [
        'coa_id',
        'tanggal',
        'reference_type',
        'reference_id',
        'reference_no',
        'movement_type',
        'nominal',
        'debit',
        'kredit',
        'balance_before',
        'balance_after',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'nominal'        => 'float',
        'debit'          => 'float',
        'kredit'         => 'float',
        'balance_before' => 'float',
        'balance_after'  => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function coa()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getReferenceAttribute(): string
    {
        return "{$this->reference_type}-{$this->reference_id}";
    }
}
