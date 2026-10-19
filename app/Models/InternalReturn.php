<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_return',
        'imr_id',
        'tanggal_return',
        'alasan',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal_return' => 'date',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($return) {
            if (empty($return->no_return)) {
                $date = $return->tanggal_return
                    ? Carbon::parse($return->tanggal_return)
                    : now();

                $count = self::whereDate('tanggal_return', $date)->count() + 1;

                $return->no_return = 'IRT-' . $date->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }

            if (empty($return->status)) {
                $return->status = 'draft';
            }

            if (empty($return->created_by) && auth()->check()) {
                $return->created_by = auth()->id();
            }
        });
    }

    public function items()
    {
        return $this->hasMany(InternalReturnItem::class);
    }

    public function imr()
    {
        return $this->belongsTo(IMRPrinting::class, 'imr_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}