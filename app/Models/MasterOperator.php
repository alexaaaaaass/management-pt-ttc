<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterOperator extends Model
{
    protected $table = 'master_operators';

    protected $fillable = [
        'nama_operator'
    ];
}