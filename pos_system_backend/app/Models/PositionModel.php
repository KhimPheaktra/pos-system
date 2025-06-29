<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionModel extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $fillable = ['id','name','status','created_by','updated_by'];

    public function updateBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

     public function createBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
