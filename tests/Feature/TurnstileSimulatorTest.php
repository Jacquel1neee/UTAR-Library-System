<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Reservation;
use App\Models\Seat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnstileSimulatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulator_scan_in_and_out_updates_reservation_statuses(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 10:00:00'));

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'S100001',
        ]);

        $area = Area::create([
            'name' => 'Main Library',
            'code' => 'MAIN',
            'description' => 'Main area',
            'total_seats' => 1,
            'color' => '#4F46E5',
            'is_active' => true,
        ]);

        $seat = Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'A-01',
            'row_label' => 'A',
            'col_position' => 1,
            'is_active' => true,
        ]);

        $now = Carbon::now();

        $reservation = Reservation::create([
            'user_id' => $student->id,
            'seat_id' => $seat->id,
            'reservation_date' => $now->toDateString(),
            'start_time' => $now->copy()->addHours(2)->format('H:i:s'),
            'end_time' => $now->copy()->addHours(3)->format('H:i:s'),
            'duration_hours' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('turnstile.simulate'), [
                'event_type' => 'entry',
                'user_id' => $student->id,
            ])
            ->assertOk()
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'checked_in',
        ]);

        $this->actingAs($admin)
            ->post(route('turnstile.simulate'), [
                'event_type' => 'exit',
                'user_id' => $student->id,
            ])
            ->assertOk()
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'completed',
        ]);

        Carbon::setTestNow();
    }

    public function test_simulator_temporary_leave_marks_reservation_as_temporary_leave(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 10:00:00'));

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'S100003',
        ]);

        $area = Area::create([
            'name' => 'Quiet Zone',
            'code' => 'QUI',
            'description' => 'Quiet area',
            'total_seats' => 1,
            'color' => '#22C55E',
            'is_active' => true,
        ]);

        $seat = Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'B-01',
            'row_label' => 'B',
            'col_position' => 1,
            'is_active' => true,
        ]);

        $now = Carbon::now();

        $reservation = Reservation::create([
            'user_id' => $student->id,
            'seat_id' => $seat->id,
            'reservation_date' => $now->toDateString(),
            'start_time' => $now->copy()->subMinutes(10)->format('H:i:s'),
            'end_time' => $now->copy()->addHour()->format('H:i:s'),
            'duration_hours' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('turnstile.simulate'), [
                'event_type' => 'entry',
                'user_id' => $student->id,
            ])
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('turnstile.simulate'), [
                'event_type' => 'temporary_leave',
                'user_id' => $student->id,
            ])
            ->assertOk()
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'temporary_leave',
        ]);

        Carbon::setTestNow();
    }

    public function test_area_counts_use_current_occupancy_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 10:00:00'));

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'S100002',
        ]);

        $area = Area::create([
            'name' => 'Reading Corner',
            'code' => 'RCA',
            'description' => 'Reading area',
            'total_seats' => 2,
            'color' => '#0EA5E9',
            'is_active' => true,
        ]);

        $seatA = Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'A-01',
            'row_label' => 'A',
            'col_position' => 1,
            'is_active' => true,
        ]);

        Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'A-02',
            'row_label' => 'A',
            'col_position' => 2,
            'is_active' => true,
        ]);

        $now = Carbon::now();

        // Stale checked_in reservation (already ended) should not count as occupied now.
        Reservation::create([
            'user_id' => $student->id,
            'seat_id' => $seatA->id,
            'reservation_date' => $now->toDateString(),
            'start_time' => $now->copy()->subHours(2)->format('H:i:s'),
            'end_time' => $now->copy()->subHour()->format('H:i:s'),
            'duration_hours' => 1,
            'status' => 'checked_in',
            'checked_in_at' => $now->copy()->subHours(2),
        ]);

        $response = $this->actingAs($student)->get(route('areas.index'))->assertOk();

        $response->assertViewHas('areas', function ($areas) use ($area) {
            $target = $areas->firstWhere('id', $area->id);

            return $target
                && $target->occupied_count === 0
                && $target->available_count === 2;
        });

        Carbon::setTestNow();
    }

    public function test_area_counts_include_future_reservation_after_simulator_check_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 10:00:00'));

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'S100004',
        ]);

        $area = Area::create([
            'name' => 'Study Hall',
            'code' => 'STU',
            'description' => 'Study area',
            'total_seats' => 1,
            'color' => '#F59E0B',
            'is_active' => true,
        ]);

        $seat = Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'C-01',
            'row_label' => 'C',
            'col_position' => 1,
            'is_active' => true,
        ]);

        $now = Carbon::now();

        Reservation::create([
            'user_id' => $student->id,
            'seat_id' => $seat->id,
            'reservation_date' => $now->toDateString(),
            'start_time' => $now->copy()->addHours(2)->format('H:i:s'),
            'end_time' => $now->copy()->addHours(3)->format('H:i:s'),
            'duration_hours' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('turnstile.simulate'), [
                'event_type' => 'entry',
                'user_id' => $student->id,
            ])
            ->assertOk();

        $response = $this->actingAs($student)->get(route('areas.index'))->assertOk();

        $response->assertViewHas('areas', function ($areas) use ($area) {
            $target = $areas->firstWhere('id', $area->id);

            return $target
                && $target->occupied_count === 1
                && $target->available_count === 0;
        });

        Carbon::setTestNow();
    }

    public function test_admin_dashboard_updates_occupied_counts_after_simulator_check_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 10:00:00'));

        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        /** @var User $student */
        $student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'S100005',
        ]);

        $area = Area::create([
            'name' => 'Admin Hall',
            'code' => 'ADH',
            'description' => 'Admin area',
            'total_seats' => 1,
            'color' => '#8B5CF6',
            'is_active' => true,
        ]);

        $seat = Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'D-01',
            'row_label' => 'D',
            'col_position' => 1,
            'is_active' => true,
        ]);

        $now = Carbon::now();

        Reservation::create([
            'user_id' => $student->id,
            'seat_id' => $seat->id,
            'reservation_date' => $now->toDateString(),
            'start_time' => $now->copy()->addHours(2)->format('H:i:s'),
            'end_time' => $now->copy()->addHours(3)->format('H:i:s'),
            'duration_hours' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('turnstile.simulate'), [
                'event_type' => 'entry',
                'user_id' => $student->id,
            ])
            ->assertOk();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $response->assertViewHas('areas', function ($areas) use ($area) {
            $target = $areas->firstWhere('id', $area->id);

            return $target
                && $target->occupied_count === 1
                && $target->available_count === 0;
        });

        $response->assertViewHas('activeReservations', 1);

        Carbon::setTestNow();
    }
}
