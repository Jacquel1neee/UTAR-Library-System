<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized access. Admin only.');
        }

        $totalUsers = User::where('role', 'student')->count();
        $totalReservations = Reservation::count();
        $activeReservations = Reservation::whereIn('status', ['checked_in', 'temporary_leave'])->count();

        $today = Carbon::now()->toDateString();
        $todayReservations = Reservation::where('reservation_date', $today)->count();

        $areas = Area::withCount(['seats' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        $now = Carbon::now();
        $currentTime = $now->toTimeString();

        foreach ($areas as $area) {
            $occupied = Reservation::whereHas('seat', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            })
                ->where('reservation_date', $today)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->whereIn('status', ['checked_in', 'temporary_leave'])
                ->count();

            $area->occupied_count = $occupied;
            $area->available_count = $area->seats_count - $occupied;
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
}