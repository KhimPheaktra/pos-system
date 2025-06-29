<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class UserClientModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user_clients';
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
        'is_active',
        'updated_by',
    ];

      public function role()
    {
        return $this->belongsTo(RoleModel::class);
    }


    public function updateBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshClientTokenModel::class, 'user_id');
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
