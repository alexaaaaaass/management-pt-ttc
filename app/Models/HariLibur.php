<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    use HasFactory;

    protected $table = 'hari_liburs';

    protected $fillable = [
        'tanggal_libur',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_libur' => 'date',
    ];
}