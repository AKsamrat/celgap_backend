<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawJournal extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'abstract',
        'keywords',
        'description',
        'status',
        'user_id',
        'speaker_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function speaker()
    {
        return $this->belongsTo(Speaker::class, 'speaker_id');
    }
}
