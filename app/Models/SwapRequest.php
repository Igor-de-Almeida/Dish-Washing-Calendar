<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapRequest extends Model
{
    protected $fillable = [
        'from_user_id', 
        'to_user_id', 
        'from_dish_day_id', 
        'to_dish_day_id', 
        'status', 
        'notes'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public static function rules()
    {
        return [
            'from_user_id' => 'required|exists:usuarios,id', 
            'to_user_id' => 'required|exists:usuarios, id|different:from_user_id', 
            'from_dish_day_id' => 'required|exists:dish_schedules, id', 
            'to_dish_day_id' => 'nullable|exists:dish_schedules, id', 
            'status' => 'required|in:pending,accepted, rejected', 
            'notes' => 'nullable|string|max:500'
        ];
    }

    public function fromUser() 
    {
        return $this->belongsTo(Usuario::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(Usuario::class, 'to_user_id');
    }

    public function fromDishDay()
    {
        return $this->belongsTo(dishSchedules::class, 'from_dish_day_id');
    }

    public function toDishDay()
    {
        return $this->belongsTo(dishSchedules::class, 'to_dish_day_id');
    }
}
