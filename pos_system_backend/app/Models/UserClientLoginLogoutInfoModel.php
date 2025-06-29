<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClientLoginLogoutInfoModel extends Model
{
    use HasFactory;

    protected $table = 'user_clients_login_logout_info';

    protected $fillable = ['id','user_id','name','email','login_at','logout_at'];
}
