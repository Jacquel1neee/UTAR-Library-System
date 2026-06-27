<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Seat;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    public function run(): void
    {
        $areas = Area::all();

        foreach ($areas as $area) {
            $seatCount = $area->total_seats;
            $rows = ceil($seatCount / 10);

            for ($i = 1; $i <= $seatCount; $i++) {
                $row = chr(65 + (int)(($i - 1) / 10)); // A, B, C, ...
                $col = (($i - 1) % 10) + 1;

                Seat::create([
                    'area_id' => $area->id,
                    'seat_number' => $row . '-' . str_pad($col, 2, '0', STR_PAD_LEFT),
                    'row_label' => $row,
                    'col_position' => $col,
                    'is_active' => true,
                ]);
            }
        }
    }
}