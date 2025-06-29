<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleModel extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = ['id','sale_date','sale_by','order_by'];


    public function user()
        {
            return $this->belongsTo(User::class, 'sale_by');
        }

        public function userClient()
        {
            return $this->belongsTo(UserClientModel::class, 'order_by');
        }

        public function details()
        {
            return $this->hasMany(SaleDetailModel::class, 'sale_id');
        }
    
        public function invoice()
        {
            return $this->hasOne(InvoiceModel::class, 'sale_id');
        }

      

        


}
