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

        $today = Carbon::today();
        $now = Carbon::now();
        $studentIds = array_values($students);
        $seatIds = $seats->pluck('id')->all();
        $records = [];

        for ($i = 0; $i < 40; $i++) {
            $seat = $seats[$i % $seats->count()];
            $seatId = $seat->id;
            $studentId = $studentIds[$i % count($studentIds)];
            $baseHour = 8 + ($i % 8);
            $startTime = sprintf('%02d:00:00', $baseHour);
            $endTime = sprintf('%02d:00:00', $baseHour + 2);

            $status = $i < 8 ? 'checked_in' : ($i < 16 ? 'temporary_leave' : 'confirmed');
            $reservation = [
                'user_id' => $studentId,
                'seat_id' => $seatId,
                'reservation_date' => $today->toDateString(),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_hours' => 2,
                'status' => $status,
            ];

            if ($status === 'checked_in') {
                $reservation['checked_in_at'] = $now->copy()->subHours(1)->toDateTimeString();
            }

            if ($status === 'temporary_leave') {
                $reservation['checked_in_at'] = $now->copy()->subHours(2)->toDateTimeString();
                $reservation['temporary_leave_started_at'] = $now->copy()->subMinutes(30)->toDateTimeString();
            }

            $records[] = $reservation;
        }

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
