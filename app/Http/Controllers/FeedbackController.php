<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function create()
    {
        return view('feedback.create');
    }

    public function storeGeneral(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        Feedback::create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('feedback.create')->with('success', 'Thank you for your feedback!');
    }

    public function store(Request $request, int $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        if ($reservation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($reservation->status !== 'completed') {
            return back()->withErrors(['feedback' => 'Feedback can only be submitted after checking out.']);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if (Feedback::where('reservation_id', $reservation->id)->exists()) {
            return back()->withErrors(['feedback' => 'Feedback has already been submitted for this reservation.']);
        }

        Feedback::create([
            'user_id' => Auth::id(),
            'reservation_id' => $reservation->id,
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}
