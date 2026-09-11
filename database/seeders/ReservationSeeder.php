<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->pluck('id')->all();
        $seats = Seat::where('is_active', true)->get();

        if (empty($students) || empty($seats)) {
            return;
        }

        $now = Carbon::now();
        $studentIds = array_values($students);
        $seatIds = $seats->pluck('id')->all();
        $records = [];

        // Current-day examples for the area dashboard and seat map:
        // the first area is full, the second area is partially occupied, and
        // the remaining seats are intentionally available.
        $activeStart = $now->copy()->subHour();
        $activeEnd = $now->copy()->addHours(2);
        $firstAreaId = $seats->first()->area_id;
        $fullAreaSeats = $seats->where('area_id', $firstAreaId)->values();
        $partiallyOccupiedSeats = $seats->where('area_id', '!=', $firstAreaId)->values();

        foreach ($fullAreaSeats as $index => $seat) {
            $status = $index === 0 ? 'temporary_leave' : 'checked_in';
            $records[] = [
                'user_id' => $studentIds[$index % count($studentIds)],
                'seat_id' => $seat->id,
                'reservation_date' => $now->toDateString(),
                'start_time' => $activeStart->format('H:i:s'),
                'end_time' => $activeEnd->format('H:i:s'),
                'duration_hours' => 3,
                'status' => $status,
                'checked_in_at' => $now->copy()->subHours(2)->toDateTimeString(),
                'temporary_leave_started_at' => $status === 'temporary_leave'
                    ? $now->copy()->subMinutes(5)->toDateTimeString()
                    : null,
            ];
        }

        // Occupy only the first eight seats in another area so it has free places.
        foreach ($partiallyOccupiedSeats->take(8) as $index => $seat) {
            $records[] = [
                'user_id' => $studentIds[($index + 1) % count($studentIds)],
                'seat_id' => $seat->id,
                'reservation_date' => $now->toDateString(),
                'start_time' => $activeStart->format('H:i:s'),
                'end_time' => $activeEnd->format('H:i:s'),
                'duration_hours' => 3,
                'status' => 'checked_in',
                'checked_in_at' => $now->copy()->subMinutes(30)->toDateTimeString(),
            ];
        }

        $today = $now->copy()->startOfDay();

        for ($i = 0; $i < 30; $i++) {
            $seatId = $seatIds[(40 + $i) % count($seatIds)];
            $studentId = $studentIds[$i % count($studentIds)];
            $date = $today->copy()->addDay(1 + ($i % 3));
            $slot = $i % 4;
            $timeSlots = [
                ['09:00:00', '12:00:00'],
                ['10:00:00', '13:00:00'],
                ['14:00:00', '17:00:00'],
                ['16:00:00', '19:00:00'],
            ];

            $status = $i % 2 === 0 ? 'confirmed' : 'pending';
            $records[] = [
                'user_id' => $studentId,
                'seat_id' => $seatId,
                'reservation_date' => $date->toDateString(),
                'start_time' => $timeSlots[$slot][0],
                'end_time' => $timeSlots[$slot][1],
                'duration_hours' => 3,
                'status' => $status,
            ];
        }

        for ($i = 0; $i < 20; $i++) {
            $seatId = $seatIds[(70 + $i) % count($seatIds)];
            $studentId = $studentIds[$i % count($studentIds)];
            $date = $today->copy()->subDay(1 + ($i % 4));
            $statuses = ['completed', 'cancelled', 'no_show'];
            $timeSlots = [
                ['08:00:00', '11:00:00'],
                ['12:00:00', '15:00:00'],
                ['15:00:00', '18:00:00'],
            ];

            $status = $statuses[$i % 3];
            $slot = $i % 3;
            $record = [
                'user_id' => $studentId,
                'seat_id' => $seatId,
                'reservation_date' => $date->toDateString(),
                'start_time' => $timeSlots[$slot][0],
                'end_time' => $timeSlots[$slot][1],
                'duration_hours' => 3,
                'status' => $status,
            ];

            if ($status === 'completed') {
                $record['checked_in_at'] = $date->copy()->setTime(8, 0)->toDateTimeString();
            }

            $records[] = $record;
        }

        foreach ($records as $record) {
            Reservation::create($record);
        }
    }
}
