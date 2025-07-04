<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceModel extends Model
{
    use HasFactory;

    protected $table = 'provinces';

    protected $fillable = ['id','name','name_in_khmer','status'];
}
