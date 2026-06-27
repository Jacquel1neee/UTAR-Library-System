<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Main Library',
                'code' => 'MAIN',
                'description' => 'Main library reading area with quiet study zones',
                'total_seats' => 250,
                'color' => '#4F46E5',
            ],
            [
                'name' => 'Reading Corner A',
                'code' => 'RC-A',
                'description' => 'Reading corner with natural lighting and comfortable seating',
                'total_seats' => 80,
                'color' => '#0EA5E9',
            ],
            [
                'name' => 'Reading Corner B',
                'code' => 'RC-B',
                'description' => 'Reading corner with group study tables',
                'total_seats' => 70,
                'color' => '#10B981',
            ],
            [
                'name' => 'Discussion Room A',
                'code' => 'DR-A',
                'description' => 'Group discussion room with whiteboard',
                'total_seats' => 50,
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Discussion Room B',
                'code' => 'DR-B',
                'description' => 'Group discussion room with projector',
                'total_seats' => 50,
                'color' => '#EF4444',
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }
    }
}