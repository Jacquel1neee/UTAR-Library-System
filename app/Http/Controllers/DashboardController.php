<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $areas = Area::withCount(['seats' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        $today = Carbon::now()->toDateString();

        foreach ($areas as $area) {
            // Count all checked_in and temporary_leave reservations for today
            $occupied = Reservation::whereHas('seat', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            })
                ->where('reservation_date', $today)
                ->whereIn('status', ['checked_in', 'temporary_leave'])
                ->count();

            $area->occupied_count = $occupied;
            $area->available_count = $area->seats_count - $occupied;
        }

        $user = Auth::user();
        $activeReservations = [];
        $upcomingReservations = [];

        if ($user) {
            $activeReservations = Reservation::with(['seat', 'seat.area'])
                ->where('user_id', $user->id)
                ->where('reservation_date', '>=', $today)
                ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'temporary_leave'])
                ->orderBy('reservation_date')
                ->orderBy('start_time')
                ->get();

            $upcomingReservations = Reservation::with(['seat', 'seat.area'])
                ->where('user_id', $user->id)
                ->where('reservation_date', '>', $today)
                ->whereIn('status', ['pending', 'confirmed'])
                ->orderBy('reservation_date')
                ->orderBy('start_time')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact('areas', 'activeReservations', 'upcomingReservations'));
    }
}