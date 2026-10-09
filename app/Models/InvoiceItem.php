<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'item',
        'jenis',
        'komponen',
        'kertas',
        'finishing',
        'qty',
        'harga',
        'total',
        'keterangan',
    ];

    protected $casts = [
        'qty' => 'float',
        'harga' => 'float',
        'total' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
