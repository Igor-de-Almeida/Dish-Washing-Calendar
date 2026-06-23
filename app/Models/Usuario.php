<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'username',
        'email',
        'password',
        'tipo'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function isAdmin() {
        return $this->tipo === 'admin';
    }

    public function dishSchedules() {
        return $this->hasMany(dishSchedules::class, 'user_id', 'id');
    }

    public function swapRequestsSent()
    {
        return $this->hasMany(SwapRequest::class, 'from_user_id');
    }

    public function swapRequestsReceived()
    {
        return $this->hasMany(SwapRequest::class, 'to_user_id');
    }
}
