<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollBorongan extends Model
{
    use HasFactory;

    protected $table = 'payroll_borongans';

    public const STATUS_OPTIONS = [
        'Draft'    => 'Draft',
        'Approved' => 'Approved',
        'Paid'     => 'Paid (Dibayarkan)',
    ];

    protected $fillable = [
        'kode_bukti',
        'periode',
        'tanggal_transaksi',
        'tanggal_mulai',
        'tanggal_selesai',
        'kode_gudang',
        'metode_pembayaran',
        'account_kas_id',
        'coa_beban_id',
        'npb_request_id',
        'total_gaji_borongan',
        'total_tunjangan',
        'total_potongan',
        'grand_total',
        'status',
        'post_to_ledger',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_transaksi'   => 'date',
        'tanggal_mulai'       => 'date',
        'tanggal_selesai'     => 'date',
        'total_gaji_borongan' => 'float',
        'total_tunjangan'     => 'float',
        'total_potongan'      => 'float',
        'grand_total'         => 'float',
        'post_to_ledger'      => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(PayrollBoronganItem::class, 'payroll_borongan_id')->orderBy('urutan')->orderBy('id');
    }

    public function accountKas()
    {
        return $this->belongsTo(MasterCOA::class, 'account_kas_id');
    }

    public function coaBeban()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_beban_id');
    }

    public function npbRequest()
    {
        return $this->belongsTo(NpbRequest::class, 'npb_request_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateKodeBukti(?Carbon $date = null): string
    {
        $d = $date ?? now();
        $prefix = 'PRB-' . $d->format('Ym') . '-';
        $seq = 1;
        $last = static::where('kode_bukti', 'like', "{$prefix}%")->orderByDesc('id')->value('kode_bukti');
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }
        while (static::where('kode_bukti', $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT))->exists()) {
            $seq++;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->kode_bukti) || strtoupper(trim($model->kode_bukti)) === 'AUTO') {
                $model->kode_bukti = static::generateKodeBukti(
                    $model->tanggal_transaksi ? Carbon::parse($model->tanggal_transaksi) : now()
                );
            }
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }
}
