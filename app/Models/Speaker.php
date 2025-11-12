<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'designation',
        'organization',
        'bio',
        'photo',
        'topic',

    ];

    // A speaker belongs to a conference
    // public function conference()
    // {
    //     return $this->belongsTo(Conference::class);
    // }
}
