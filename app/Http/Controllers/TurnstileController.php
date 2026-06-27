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

        // Handle reservation logic based on scan type
        $today = now()->toDateString();
        $currentTime = now()->toTimeString();

        if ($eventType === 'entry') {
            // Check if user has a pending reservation for today
            $pendingReservations = Reservation::where('user_id', $user->id)
                ->where('reservation_date', $today)
                ->where('status', 'pending')
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->get();

            foreach ($pendingReservations as $reservation) {
                // Check if within 15 minutes of start time
                $startTime = Carbon::parse($reservation->start_time);
                $minutesDiff = $startTime->diffInMinutes(now());

                if ($minutesDiff <= 15) {
                    $reservation->update([
                        'status' => 'checked_in',
                        'checked_in_at' => now(),
                    ]);
                } else {
                    // Too late - release the seat
                    $reservation->update(['status' => 'no_show']);
                }
            }

            // Also handle return from temporary leave
            $leaveReservations = Reservation::where('user_id', $user->id)
                ->where('reservation_date', $today)
                ->where('status', 'temporary_leave')
                ->get();

            foreach ($leaveReservations as $reservation) {
                $leaveStarted = Carbon::parse($reservation->temporary_leave_started_at);
                $minutesLeft = $leaveStarted->diffInMinutes(now());

                if ($minutesLeft <= 15) {
                    $reservation->update([
                        'status' => 'checked_in',
                        'temporary_leave_ended_at' => now(),
                    ]);
                } else {
                    $reservation->update([
                        'status' => 'no_show',
                        'temporary_leave_ended_at' => now(),
                    ]);
                }
            }
        }

        if ($eventType === 'exit') {
            // Release any checked_in or temporary_leave reservations
            $activeReservations = Reservation::where('user_id', $user->id)
                ->where('reservation_date', $today)
                ->whereIn('status', ['checked_in', 'temporary_leave'])
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->get();

            foreach ($activeReservations as $reservation) {
                $reservation->update([
                    'status' => 'completed',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Scan simulated successfully',
            'event' => $event,
        ]);
    }

    public function showSimulator()
    {
        $users = User::where('role', 'student')->get();
        return view('turnstile.simulator', compact('users'));
    }
}