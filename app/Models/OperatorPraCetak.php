<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorPraCetak extends Model
{
    protected $table = 'operator_pracetak';

    protected $fillable = [
        'nama_operator',
    ];
}