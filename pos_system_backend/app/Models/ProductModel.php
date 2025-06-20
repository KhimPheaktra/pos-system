<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = ['id','name','category_id','price','discount','current_qty','image','description'];
    
    public function category()
    {
        return $this->belongsTo(CategoryModel::class);
    }


}
