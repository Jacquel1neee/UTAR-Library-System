@extends('layouts.app')

@section('title', 'All Reservations')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">All Reservations</h1>
            <p class="text-muted small">Complete reservation history for the library</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="card-custom p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="py-2 px-4">User</th>
                    <th class="py-2">Seat</th>
                    <th class="py-2">Area</th>
                    <th class="py-2">Date</th>
                    <th class="py-2">Time</th>
                    <th class="py-2">Status</th>
                    <th class="py-2 text-end pe-4">Booked At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td class="py-2 px-4">{{ $reservation->user->name }}</td>
                        <td class="py-2">{{ $reservation->seat->seat_number }}</td>
                        <td class="py-2">{{ $reservation->seat->area->name }}</td>
                        <td class="py-2">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d') }}</td>
                        <td class="py-2">{{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}</td>
                        <td class="py-2"><span class="status-badge {{ $reservation->status }}">{{ $reservation->status_label }}</span></td>
                        <td class="py-2 text-end pe-4 text-muted small">{{ \Carbon\Carbon::parse($reservation->created_at)->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No reservations found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $reservations->links() }}
</div>
@endsection
