<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefreshClientTokenModel extends Model
{
    use HasFactory;

    protected $table = 'refresh_tokens_clients';
    protected $fillable = ['user_id', 'token', 'expires_at','ip','user_agent'];

     protected $casts = [
        'expires_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(UserClientModel::class);
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}
