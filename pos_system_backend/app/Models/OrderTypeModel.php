<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTypeModel extends Model
{
    use HasFactory;

    protected $table = 'order_type';

    protected $fillable = ['id','order_type','note'];
}
