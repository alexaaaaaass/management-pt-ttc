<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_customer',
        'nama_customer',
        'alamat_lengkap',
        'alamat_kedua',
        'alamat_ketiga',
        'kode_group',
        'nama_group',
    ];
}