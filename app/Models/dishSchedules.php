<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class dishSchedules extends Model
{
    use HasFactory;

    protected $table = 'dish_schedules';

    protected $fillable = [
        'house_id',
        'user_id',
        'scheduled_date',
        'status',
        'notes',
        'shift',
        'photo_path'
    ];

    protected $casts = [
        'scheduled_date' => 'date'
    ];

    public function rules()
    {
        return [
            'user_id' => 'required|exists:usuario,id',
            'scheduled_date' => 'required|date',
            'status' => 'in:pending,completed,missed,swapped',
            'notes' => 'nullable|string|max:500',
            'shift' => 'in:full,tarde,noite',
            'photo_path' => 'nullable|string|max:255'
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'id');
    }

    public function House() 
    {
        return $this->belongsTo(House::class);
    }
}
