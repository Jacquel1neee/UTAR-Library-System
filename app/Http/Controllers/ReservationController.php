<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function create()
    {
        return view('reservations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'seat_id' => 'required|exists:seats,id',
            'reservation_date' => 'required|date|after_or_equal:today|before_or_equal:' . Carbon::now()->addDays(7)->toDateString(),
            'start_time' => 'required|date_format:H:i',
            'duration_hours' => 'required|integer|in:1,2,3,4,6,8',
        ]);

        $seat = Seat::findOrFail($request->seat_id);
        $startTime = $request->start_time;
        $durationHours = (int) $request->duration_hours;
        $endTime = Carbon::createFromFormat('H:i', $startTime)->addHours($durationHours)->format('H:i');

        if (!$seat->isAvailableFor($request->reservation_date, $startTime, $endTime)) {
            return back()->withErrors([
                'seat_id' => 'This seat is already booked for the selected time slot.'
            ]);
        }

        $user = Auth::user();
        $overlapping = Reservation::where('user_id', $user->id)
            ->where('reservation_date', $request->reservation_date)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'temporary_leave'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        if ($overlapping) {
            return back()->withErrors([
                'reservation_date' => 'You already have a reservation that overlaps with this time slot.'
            ]);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'seat_id' => $request->seat_id,
            'reservation_date' => $request->reservation_date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_hours' => $durationHours,
            'status' => 'pending',
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservation created successfully! Please check in within 15 minutes.');
    }

    public function show(int $id)
    {
        $reservation = Reservation::with(['seat', 'seat.area', 'user'])
            ->findOrFail($id);

        $user = Auth::user();
        if ($reservation->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        return view('reservations.show', compact('reservation'));
    }

    public function index()
    {
        $user = Auth::user();

        $reservations = Reservation::with(['seat', 'seat.area'])
            ->where('user_id', $user->id)
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('reservations.index', compact('reservations'));
    }

    public function cancel(int $id)
    {
        $reservation = Reservation::findOrFail($id);

        $user = Auth::user();
        if ($reservation->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->withErrors([
                'status' => 'This reservation cannot be cancelled.'
            ]);
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('reservations.index')
            ->with('success', 'Reservation cancelled successfully.');
    }

    public function checkIn(int $id)
    {
        $reservation = Reservation::findOrFail($id);

        $user = Auth::user();
        if ($reservation->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        if ($reservation->status !== 'pending' && $reservation->status !== 'confirmed') {
            return back()->withErrors([
                'status' => 'This reservation cannot be checked in.'
            ]);
        }

        $reservation->update([
            'status' => 'checked_in',
            'checked_in_at' => now(),
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Checked in successfully!');
    }

    public function temporaryLeave(int $id)
    {
        $reservation = Reservation::findOrFail($id);

        $user = Auth::user();
        if ($reservation->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        if ($reservation->status !== 'checked_in') {
            return back()->withErrors([
                'status' => 'You must be checked in to use temporary leave.'
            ]);
        }

        $reservation->update([
            'status' => 'temporary_leave',
            'temporary_leave_started_at' => now(),
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Temporary leave started. You have 15 minutes to return.');
    }

    public function returnFromLeave(int $id)
    {
        $reservation = Reservation::findOrFail($id);

        $user = Auth::user();
        if ($reservation->user_id !== $user->id && $user->role !== 'admin') {
            abort(403);
        }

        if ($reservation->status !== 'temporary_leave') {
            return back()->withErrors([
                'status' => 'You are not on temporary leave.'
            ]);
        }

        $leaveStarted = Carbon::parse($reservation->temporary_leave_started_at);
        $minutesLeft = $leaveStarted->diffInMinutes(now());

        if ($minutesLeft > 15) {
            $reservation->update([
                'status' => 'no_show',
                'temporary_leave_ended_at' => now(),
            ]);

            return redirect()->route('reservations.show', $reservation)
                ->withErrors([
                    'status' => 'You exceeded the 15-minute limit. Your seat has been released.'
                ]);
        }

        $reservation->update([
            'status' => 'checked_in',
            'temporary_leave_ended_at' => now(),
        ]);

        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Welcome back! Your seat is still reserved.');
    }
}