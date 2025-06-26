<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShiftModel extends Model
{
    use HasFactory;

    protected $table = 'employee_shifts';

    protected $fillable = ['id','start_by','start_at','end_at','end_by','amount_input','total_item_sale','total_amount','status'];

    public function startUser() {
        return $this->belongsTo(User::class, 'start_by');
    }
    public function endUser() {
        return $this->belongsTo(User::class, 'end_by');
    }


}
