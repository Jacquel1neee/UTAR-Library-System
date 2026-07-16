<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\TurnstileEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TurnstileController extends Controller
{
    public function simulateScan(Request $request)
    {
        $request->validate([
            'event_type' => 'required|in:entry,exit,temporary_leave',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $eventType = $request->event_type;

        // Create turnstile event
        $event = TurnstileEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'event_time' => now(),
            'is_simulated' => true,
            'metadata' => ['simulated_by' => Auth::id() ?? 'system'],
        ]);

        $today = now()->toDateString();
        $message = '';

        switch ($eventType) {
            case 'entry':
                $message = $this->handleEntry($user, $today);
                break;
            case 'exit':
                $message = $this->handleExit($user, $today);
                break;
            case 'temporary_leave':
                $message = $this->handleTemporaryLeave($user, $today);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'event' => $event,
        ]);
    }

    private function handleEntry($user, $today)
    {
        $messages = [];

        // 1. Check if user has any active checked_in reservation
        $checkedIn = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->where('status', 'checked_in')
            ->exists();

        if ($checkedIn) {
            $messages[] = "You already have an active reservation. Please check out first.";
            return implode(' | ', $messages);
        }

        // 2. Handle return from temporary leave
        $leaveReservations = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->where('status', 'temporary_leave')
            ->orderBy('start_time')
            ->get();

        foreach ($leaveReservations as $reservation) {
            $now = Carbon::now();
            $leaveStarted = $reservation->temporary_leave_started_at
                ? Carbon::parse($reservation->temporary_leave_started_at)
                : $now;
            $minutesPassed = $leaveStarted->diffInMinutes($now);

            if ($minutesPassed <= 15) {
                $reservation->update([
                    'status' => 'checked_in',
                    'temporary_leave_ended_at' => $now,
                ]);
                $messages[] = "Welcome back! Returned from temporary leave for seat {$reservation->seat->seat_number}";
            } else {
                $reservation->update([
                    'status' => 'no_show',
                    'temporary_leave_ended_at' => $now,
                ]);
                $messages[] = "Temporary leave expired for seat {$reservation->seat->seat_number} (exceeded 15 minutes)";
            }

            return implode(' | ', $messages);
        }

        // 3. Auto-check in a pending/confirmed reservation.
        $reservations = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get();

        if ($reservations->isEmpty()) {
            $messages[] = "No pending reservations found for today.";
            return implode(' | ', $messages);
        }

        foreach ($reservations as $reservation) {
            $now = Carbon::now();
            $reservation->update([
                'status' => 'checked_in',
                'checked_in_at' => $now,
            ]);
            $messages[] = "Checked in to seat {$reservation->seat->seat_number} at {$reservation->seat->area->name}";
            return implode(' | ', $messages);
        }

        return "No pending reservations found for today.";
    }

    private function handleExit($user, $today)
    {
        $messages = [];

        // Release all active reservations (checked_in or temporary_leave)
        $activeReservations = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['checked_in', 'temporary_leave'])
            ->get();

        if ($activeReservations->isEmpty()) {
            $messages[] = "No active reservations to release.";
            return implode(' | ', $messages);
        }

        foreach ($activeReservations as $reservation) {
            $reservation->update([
                'status' => 'completed',
                'temporary_leave_ended_at' => now(),
            ]);
            $messages[] = "Released seat {$reservation->seat->seat_number}";
        }

        return implode(' | ', $messages);
    }

    private function handleTemporaryLeave($user, $today)
    {
        $reservation = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->where('status', 'checked_in')
            ->orderBy('start_time')
            ->first();

        if (!$reservation) {
            return "No checked-in reservation found for temporary leave.";
        }

        $now = now();

        $reservation->update([
            'status' => 'temporary_leave',
            'temporary_leave_started_at' => $now,
        ]);

        return "Temporary leave started for seat {$reservation->seat->seat_number}. Return within 15 minutes.";
    }

    public function showSimulator()
    {
        $users = User::where('role', 'student')->get();
        
        return view('turnstile.simulator', compact('users'));
    }
}