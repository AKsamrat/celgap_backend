<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpringWorkshopTrainee extends Model
{
    use HasFactory;

    protected $table = 'spring_workshop_trainees';

    // ✅ Mass assignable fields
    protected $fillable = [
        'title',
        'venue',
        'date',
        'time',
        'description',
        'category',
        'duration',
        'status',
        'user_id',
        'speaker_id',
    ];

    // ✅ Relationships

    // A trainee is created by a user (admin or organizer)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A trainee program has one speaker
    public function speaker()
    {
        return $this->belongsTo(Speaker::class);
    }
}
