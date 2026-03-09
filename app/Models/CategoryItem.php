<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryItem extends Model
{
    protected $table = 'category_items';

    protected $fillable = [
        'kode_item_category',
        'nama_category'
    ];

    public function typeItems()
    {
        return $this->hasMany(TypeItem::class,'category_item_id');
    }

    // relasi ke master item
    public function items()
    {
        return $this->hasMany(MasterItem::class,'category_item_id');
    }
}