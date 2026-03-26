<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinishGoodItemMaterial extends Model
{
    protected $fillable = [
        'finish_good_item_id',
        'master_item_id',
        'departemen_id',
        'qty',
        'waste',
        'keterangan'
    ];

    public function finishGoodItem()
    {
        return $this->belongsTo(FinishGoodItem::class);
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }

    public function departemen()
    {
        return $this->belongsTo(MasterDepartemen::class, 'departemen_id');
    }
}