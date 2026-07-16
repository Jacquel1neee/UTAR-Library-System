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

        $now = Carbon::now();

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

        return view('areas.index', compact('areas'));
    }

    public function show(int $id)
    {
        $area = Area::with(['seats' => function ($query) {
            $query->where('is_active', true)->orderBy('row_label')->orderBy('col_position');
        }])->findOrFail($id);

        $now = Carbon::now();

        foreach ($area->seats as $seat) {
            $isOccupied = Reservation::where('seat_id', $seat->id)
                ->occupyingNow($now)
                ->exists();

            $seat->is_occupied_now = $isOccupied;
        }

        return view('areas.show', compact('area'));
    }
}