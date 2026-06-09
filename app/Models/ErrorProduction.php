<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErrorProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_error',
        'nama_error',
    ];
    public function printings()
{
    return $this->hasMany(Printing::class);
}
public function masterFinishings()
{
    return $this->hasMany(MasterFinishing::class);
}

}