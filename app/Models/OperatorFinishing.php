<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorFinishing extends Model
{
    protected $table = 'operator_finishings';

    protected $fillable = [
        'nama_operator'
    ];
}