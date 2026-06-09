<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blokir extends Model
{
    use HasFactory;

    protected $table = 'blokir';

    protected $fillable = [
        'no_blokir',
        'no_spk',
        'tanggal_blokir',
        'operator',
        'qty_blokir',
        'customer',
        'keterangan',
    ];
}