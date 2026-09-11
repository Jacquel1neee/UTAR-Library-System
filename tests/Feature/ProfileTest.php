<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'student_id' => 'S123456',
            'role' => 'student',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee($user->name);
    }

    public function test_user_can_update_profile_information(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'student_id' => 'OLD001',
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'student_id' => 'NEW001',
            'phone_number' => '0123456789',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'student_id' => 'NEW001',
            'phone_number' => '0123456789',
        ]);
    }

    public function test_password_change_requires_current_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
            'student_id' => 'PASS001',
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'student_id' => $user->student_id,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
