<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::withCount(['seats' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        $today = Carbon::now()->toDateString();

        foreach ($areas as $area) {
            $occupied = Reservation::whereHas('seat', function ($query) use ($area) {
                $query->where('area_id', $area->id);
            })
                ->where('reservation_date', $today)
                ->whereIn('status', ['checked_in', 'temporary_leave'])
                ->count();

            $area->occupied_count = $occupied;
            $area->available_count = $area->seats_count - $occupied;
        }

        return view('areas.index', compact('areas'));
    }

    public function show(int $id)
    {
        $area = Area::with(['seats' => function ($query) {
            $query->where('is_active', true)->orderBy('row_label')->orderBy('col_position');
        }])->findOrFail($id);

        $today = Carbon::now()->toDateString();

        foreach ($area->seats as $seat) {
            $isOccupied = Reservation::where('seat_id', $seat->id)
                ->where('reservation_date', $today)
                ->whereIn('status', ['checked_in', 'temporary_leave'])
                ->exists();

            $seat->is_occupied_now = $isOccupied;
        }

        return view('areas.show', compact('area'));
    }
}