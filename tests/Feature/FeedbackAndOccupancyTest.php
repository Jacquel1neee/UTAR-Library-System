<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Reservation;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackAndOccupancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_general_feedback_without_a_reservation(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)->post(route('feedback.store'), [
            'rating' => 4,
            'comment' => 'The study area was comfortable.',
        ])->assertRedirect(route('feedback.create'))->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'user_id' => $user->id,
            'reservation_id' => null,
            'rating' => 4,
        ]);
    }

    public function test_completed_reservation_owner_can_submit_feedback_once(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'student']);
        $seat = $this->createSeat();
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'seat_id' => $seat->id,
            'reservation_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 1,
            'status' => 'completed',
        ]);

        $this->actingAs($user)->post(route('reservations.feedback', $reservation), [
            'rating' => 5,
            'comment' => 'Very comfortable.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'reservation_id' => $reservation->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $this->actingAs($user)->post(route('reservations.feedback', $reservation), [
            'rating' => 4,
        ])->assertSessionHasErrors('feedback');

        $this->assertDatabaseCount('feedback', 1);
    }

    public function test_admin_can_view_occupancy_report_and_download_csv(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);
        /** @var User $user */
        $user = User::factory()->create(['role' => 'student']);
        $seat = $this->createSeat();
        Reservation::create([
            'user_id' => $user->id,
            'seat_id' => $seat->id,
            'reservation_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration_hours' => 1,
            'status' => 'completed',
        ]);

        $this->actingAs($admin)->get(route('admin.occupancy', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertOk()->assertViewHas('report', function ($report) {
            return $report->first()['occupied_seats'] === 1
                && $report->first()['occupancy_percentage'] === 100.0;
        });

        $this->actingAs($admin)->get(route('admin.occupancy.export', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function createSeat(): Seat
    {
        $area = Area::create([
            'name' => 'Test Area',
            'code' => uniqid('TST'),
            'description' => 'Test area',
            'total_seats' => 1,
            'color' => '#4F46E5',
            'is_active' => true,
        ]);

        return Seat::create([
            'area_id' => $area->id,
            'seat_number' => 'A-01',
            'row_label' => 'A',
            'col_position' => 1,
            'is_active' => true,
        ]);
    }
}
