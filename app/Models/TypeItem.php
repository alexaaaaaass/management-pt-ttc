<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeItem extends Model
{
    protected $table = 'type_items';

    protected $fillable = [
        'kode_type_item',
        'nama_type_item',
        'category_item_id'
    ];

    public function category()
    {
        return $this->belongsTo(CategoryItem::class,'category_item_id');
    }

    // relasi ke master item
    public function items()
    {
        return $this->hasMany(MasterItem::class,'type_item_id');
    }
}