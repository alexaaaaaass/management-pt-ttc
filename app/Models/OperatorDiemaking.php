<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorDieMaking extends Model
{
    protected $table = 'operator_diemaking';

    protected $fillable = [
        'nama_operator',
    ];
}