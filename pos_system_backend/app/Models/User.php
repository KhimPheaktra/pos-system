<?php

namespace App\Models;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'image',
        'created_by',
        'updated_by',
        'is_active'
    ];

    public function role()
    {
        return $this->belongsTo(RoleModel::class);
    }

    public function updateBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

     public function createBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function refreshTokens()
    {
        return $this->hasMany(RefreshTokenModel::class, 'user_id');
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
