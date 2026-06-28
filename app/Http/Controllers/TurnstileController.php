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
            'event_type' => 'required|in:entry,exit',
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

        if ($eventType === 'entry') {
            $message = $this->handleEntry($user, $today);
        }

        if ($eventType === 'exit') {
            $message = $this->handleExit($user, $today);
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
            ->where('reservation_date', $today)
            ->where('status', 'checked_in')
            ->exists();

        if ($checkedIn) {
            $messages[] = "You already have an active reservation. Please check out first.";
            return implode(' | ', $messages);
        }

        // 2. Handle return from temporary leave
        $leaveReservations = Reservation::where('user_id', $user->id)
            ->where('reservation_date', $today)
            ->where('status', 'temporary_leave')
            ->get();

        foreach ($leaveReservations as $reservation) {
            $leaveStarted = Carbon::parse($reservation->temporary_leave_started_at);
            $minutesPassed = $leaveStarted->diffInMinutes(now());

            if ($minutesPassed <= 15) {
                $reservation->update([
                    'status' => 'checked_in',
                    'temporary_leave_ended_at' => now(),
                ]);
                $messages[] = "Welcome back! Returned from temporary leave for seat {$reservation->seat->seat_number}";
            } else {
                $reservation->update([
                    'status' => 'no_show',
                    'temporary_leave_ended_at' => now(),
                ]);
                $messages[] = "Temporary leave expired for seat {$reservation->seat->seat_number} (exceeded 15 minutes)";
            }
            
            return implode(' | ', $messages);
        }

        // 3. Auto check in the nearest pending/confirmed reservation
        $now = Carbon::now();
        
        // Find the closest reservation (start time closest to now)
        $reservation = Reservation::where('user_id', $user->id)
            ->where('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->first();

        if (!$reservation) {
            $messages[] = "No pending reservations found for today.";
            return implode(' | ', $messages);
        }

        $startTime = Carbon::parse($reservation->start_time);
        $minutesDiff = $startTime->diffInMinutes($now, false);

        // Check if within acceptable range (15 min before to 60 min after)
        if ($minutesDiff >= -15 && $minutesDiff <= 60) {
            $reservation->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
            ]);
            $messages[] = "Checked in to seat {$reservation->seat->seat_number} at {$reservation->seat->area->name}";
        } else if ($minutesDiff < -15) {
            $messages[] = "Too early! Your reservation starts at " . Carbon::parse($reservation->start_time)->format('h:i A') . ". Please wait.";
        } else if ($minutesDiff > 60) {
            $reservation->update([
                'status' => 'no_show',
            ]);
            $messages[] = "Reservation for seat {$reservation->seat->seat_number} has expired (exceeded 60 minutes grace period)";
        }

        return implode(' | ', $messages);
    }

    private function handleExit($user, $today)
    {
        $messages = [];

        // Release all active reservations (checked_in or temporary_leave)
        $activeReservations = Reservation::where('user_id', $user->id)
            ->where('reservation_date', $today)
            ->whereIn('status', ['checked_in', 'temporary_leave'])
            ->get();

        if ($activeReservations->isEmpty()) {
            $messages[] = "No active reservations to release.";
            return implode(' | ', $messages);
        }

        foreach ($activeReservations as $reservation) {
            $reservation->update([
                'status' => 'completed',
            ]);
            $messages[] = "Released seat {$reservation->seat->seat_number}";
        }

        return implode(' | ', $messages);
    }

    public function showSimulator()
    {
        $users = User::where('role', 'student')->get();
        
        return view('turnstile.simulator', compact('users'));
    }
}