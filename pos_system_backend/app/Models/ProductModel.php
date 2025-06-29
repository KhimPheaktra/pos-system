<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $fillable = ['id','code','name','category_id','price','discount','price_afer_discount','current_qty','image','description','created_by','updated_by'];
    
    public function category()
    {
        return $this->belongsTo(CategoryModel::class);
    }
        public function updateBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

     public function createBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
