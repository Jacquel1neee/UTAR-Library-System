<?php

namespace Tests\Feature;

use App\Models\Reservation;
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
}
