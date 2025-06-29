<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = ['id','first_name','last_name','gender_id','dob','pob','salary','image','status','position_id','created_by','updated_by'];

    public function gender(){
        return $this->belongsTo(GenderModel::class);
    }
    public function province(){
        return $this->belongsTo(ProvinceModel::class,'pob');
    }

    public function position(){
        return $this->belongsTo(PositionModel::class);
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
