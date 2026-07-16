<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Reservation;
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
}