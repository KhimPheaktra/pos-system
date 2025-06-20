<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRateModel extends Model
{
    use HasFactory;

    protected $table = 'exchange_rate';
    protected $fillable = ['id','base_currency','target_currency','rate','note'];
}
