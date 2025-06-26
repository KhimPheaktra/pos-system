<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetailModel extends Model
{
    use HasFactory;

    protected $table = 'sale_details';

    protected $fillable = ['id','sale_id','product_id','qty','price','total_price_usd','total_price_riel','discount','order_type_id','status','amount_take_usd','amount_take_riel','amount_change_usd','amount_take_riel'];


    public function orderType()
        {
            return $this->belongsTo(OrderTypeModel::class, 'order_type_id');
        }

    public function product() {
            return $this->belongsTo(ProductModel::class);
        }

}
