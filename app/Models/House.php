<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    protected $fillable = ['nome', 'invite_code', 'owner_id'];

    public function owner()
    {
        return $this->belongsTo(Usuario::class, 'owner_id');
    }

    public function users()
    {
        return $this->hasMany(Usuario::class);
    }

    public function dishSchedules()
    {
        return $this->hasMany(dishSchedules::class);
    }

    public function swapRequests()
    {
        return $this->hasMany(SwapRequest::class);
    }
}
