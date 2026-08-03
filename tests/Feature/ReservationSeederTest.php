<?php

namespace Tests\Feature;

use App\Models\Reservation;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_reservations_for_all_statuses(): void
    {
        $this->seed(DatabaseSeeder::class);

        $statuses = ['pending', 'confirmed', 'checked_in', 'temporary_leave', 'completed', 'cancelled', 'no_show'];

        foreach ($statuses as $status) {
            $this->assertTrue(
                Reservation::where('status', $status)->exists(),
                "Expected at least one reservation with status '{$status}'."
            );
        }
    }

    public function test_temporary_leave_reservation_is_only_considered_occupied_during_its_active_window(): void
    {
        $now = Carbon::parse('2026-08-03 14:00:00');

        $user = \App\Models\User::create([
            'name' => 'Temp User',
            'email' => 'temp@example.com',
            'password' => bcrypt('password'),
            'student_id' => 'TEMP001',
            'phone_number' => '0111111111',
            'role' => 'student',
            'is_active' => true,
        ]);

        $area = \App\Models\Area::create([
            'name' => 'Temp Area',
            'code' => 'TMP',
            'description' => 'Temporary area',
            'total_seats' => 1,
            'color' => '#111111',
        ]);

        $seat = \App\Models\Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'A-01',
            'row_label' => 'A',
            'col_position' => 1,
            'is_active' => true,
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'seat_id' => $seat->id,
            'reservation_date' => $now->toDateString(),
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'duration_hours' => 2,
            'status' => 'temporary_leave',
        ]);

        $this->assertFalse(
            Reservation::query()->occupyingNow($now->copy()->subHour())->exists(),
            'A temporary leave reservation should not be marked occupied before its slot begins.'
        );

        $this->assertTrue(
            Reservation::query()->occupyingNow($now->copy()->setTime(15, 30))->exists(),
            'A temporary leave reservation should be marked occupied once the time slot is active.'
        );
    }
}
