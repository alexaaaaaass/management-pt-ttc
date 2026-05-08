<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodeBayar extends Model
{
    use HasFactory;

    protected $table = 'metode_bayars';

    protected $fillable = [
        'kode_pembayaran',
        'metode_pembayaran',
    ];
}