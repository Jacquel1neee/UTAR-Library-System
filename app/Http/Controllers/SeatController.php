<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function getAvailableSeats(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'date' => 'required|date|after_or_equal:today|before_or_equal:' . Carbon::now()->addDays(7)->toDateString(),
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $areaId = $request->area_id;
        $date = $request->date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        $seats = Seat::where('area_id', $areaId)
            ->where('is_active', true)
            ->get();

        $availableSeats = [];

        foreach ($seats as $seat) {
            if ($seat->isAvailableFor($date, $startTime, $endTime)) {
                $availableSeats[] = $seat;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $availableSeats,
            'count' => count($availableSeats),
        ]);
    }
}