<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\Seat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized access. Admin only.');
        }

        $totalUsers = User::where('role', 'student')->count();
        $totalReservations = Reservation::count();
        $now = Carbon::now();
        $activeReservations = Reservation::query()
            ->occupyingNow($now)
            ->distinct('seat_id')
            ->count('seat_id');

        $today = Carbon::now()->toDateString();
        $todayReservations = Reservation::where('reservation_date', $today)->count();

        $areas = Area::withCount(['seats' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        foreach ($areas as $area) {
            $occupied = Reservation::query()
                ->whereHas('seat', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            })
                ->occupyingNow($now)
                ->distinct('seat_id')
                ->count('seat_id');

            $area->occupied_count = $occupied;
            $area->available_count = max($area->seats_count - $occupied, 0);
        }

        $recentReservations = Reservation::with(['user', 'seat', 'seat.area'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalReservations',
            'activeReservations',
            'todayReservations',
            'areas',
            'recentReservations'
        ));
    }

    public function allReservations()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized access. Admin only.');
        }

        $reservations = Reservation::with(['user', 'seat', 'seat.area'])
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('admin.reservations', compact('reservations'));
    }

    public function feedback()
    {
        $this->authorizeAdmin();

        $feedback = Feedback::with(['user', 'reservation.seat'])
            ->latest()
            ->paginate(20);

        return view('admin.feedback', compact('feedback'));
    }

    public function occupancy(Request $request)
    {
        $this->authorizeAdmin();

        [$from, $to] = $this->reportDates($request);
        $report = $this->occupancyReport($from, $to);
        $averageOccupancy = $report->avg('occupancy_percentage') ?? 0;
        $peakOccupied = $report->max('occupied_seats') ?? 0;

        return view('admin.occupancy', compact('report', 'from', 'to', 'averageOccupancy', 'peakOccupied'));
    }

    public function exportOccupancy(Request $request)
    {
        $this->authorizeAdmin();

        [$from, $to] = $this->reportDates($request);
        $report = $this->occupancyReport($from, $to);
        $filename = 'occupancy-report-' . $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Active Seats', 'Occupied Seats', 'Occupancy %', 'Reservations']);
            foreach ($report as $row) {
                fputcsv($handle, [$row['date']->format('Y-m-d'), $row['active_seats'], $row['occupied_seats'], $row['occupancy_percentage'], $row['reservations']]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized access. Admin only.');
        }
    }

    private function reportDates(Request $request): array
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = Carbon::parse($validated['from'] ?? now()->subDays(6)->toDateString())->startOfDay();
        $to = Carbon::parse($validated['to'] ?? now()->toDateString())->startOfDay();

        if ($to->lt($from) || $from->diffInDays($to) > 31) {
            abort(422, 'Please select a date range of 32 days or less.');
        }

        return [$from, $to];
    }

    private function occupancyReport(Carbon $from, Carbon $to)
    {
        $activeSeats = Seat::where('is_active', true)->count();
        $report = collect();

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $reservations = Reservation::whereDate('reservation_date', $date->toDateString())
                ->whereIn('status', ['checked_in', 'temporary_leave', 'completed'])
                ->get(['seat_id']);
            $occupiedSeats = $reservations->pluck('seat_id')->unique()->count();

            $report->push([
                'date' => $date->copy(),
                'active_seats' => $activeSeats,
                'occupied_seats' => $occupiedSeats,
                'occupancy_percentage' => $activeSeats > 0 ? round(($occupiedSeats / $activeSeats) * 100, 1) : 0,
                'reservations' => $reservations->count(),
            ]);
        }

        return $report;
    }
}