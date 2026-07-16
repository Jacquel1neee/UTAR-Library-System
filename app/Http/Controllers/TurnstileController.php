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
        $now = Carbon::now();

        // 1. Check if user has any active checked_in reservation
        $checkedIn = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->where('status', 'checked_in')
            ->where('end_time', '>=', $now->toTimeString())
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
        // Expired reservations are marked as no_show and we continue checking next ones.
        $reservations = Reservation::where('user_id', $user->id)
            ->whereDate('reservation_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get();

        if ($reservations->isEmpty()) {
            $messages[] = "No pending reservations found for today.";
            return implode(' | ', $messages);
        }

        $tooEarlyMessage = null;

        foreach ($reservations as $reservation) {
            $rawStartTime = (string) ($reservation->getRawOriginal('start_time') ?: $reservation->start_time);

            $timePart = Carbon::parse($rawStartTime)->format('H:i:s');

            $startTime = Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $timePart);
            $minutesDiff = $startTime->diffInMinutes($now, false);

            // Too early for this reservation, and all following ones will be even later.
            if ($minutesDiff < -15) {
                $tooEarlyMessage = "Too early! Your reservation starts at " . $startTime->format('h:i A') . ". Please wait.";
                break;
            }

            if ($minutesDiff > 60) {
                $reservation->update([
                    'status' => 'no_show',
                ]);
                $messages[] = "Reservation for seat {$reservation->seat->seat_number} has expired (exceeded 60 minutes grace period)";
                continue;
            }

            $reservation->update([
                'status' => 'checked_in',
                'checked_in_at' => $now,
            ]);
            $messages[] = "Checked in to seat {$reservation->seat->seat_number} at {$reservation->seat->area->name}";
            return implode(' | ', $messages);
        }

        if (!empty($messages)) {
            return implode(' | ', $messages);
        }

        if ($tooEarlyMessage) {
            return $tooEarlyMessage;
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

    public function showSimulator()
    {
        $users = User::where('role', 'student')->get();
        
        return view('turnstile.simulator', compact('users'));
    }
}