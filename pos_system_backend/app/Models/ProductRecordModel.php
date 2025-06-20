<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRecordModel extends Model
{
    use HasFactory;

    protected $table = 'product_records';

    protected $fillable = ['id','product_id','old_name','new_name','old_price','new_price','old_qty','new_qty','updated_by'];
}
