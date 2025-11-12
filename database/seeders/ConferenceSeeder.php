<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conference;

class ConferenceSeeder extends Seeder
{
    public function run(): void
    {
        Conference::create([
            'title' => 'Tech Summit 2025',
            'date' => '2025-11-25',
            'venue' => 'Dhaka Convention Center',
            'time' => '10:00:00',
            'description' => 'A conference on latest tech trends.',
            'status' => 'upcoming',
            'category' => 'Technology',
            'user_id' => 202,
            'speaker_id' => 2
        ]);
    }
}
