<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    use HasFactory;

    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'coa_id',
        'keterangan',
        'debit',
        'kredit',
    ];

    protected $casts = [
        'debit'  => 'float',
        'kredit' => 'float',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function coa()
    {
        return $this->belongsTo(MasterCOA::class, 'coa_id');
    }
}

