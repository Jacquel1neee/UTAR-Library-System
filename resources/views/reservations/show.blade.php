@extends('layouts.app')

@section('title', 'Reservation Details')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('reservations.index') }}" class="text-decoration-none">My Reservations</a></li>
                    <li class="breadcrumb-item active">#{{ $reservation->id }}</li>
                </ol>
            </nav>
            <h1 class="mb-1">Reservation Details</h1>
        </div>
        <div class="col-auto">
            <span class="status-badge {{ $reservation->status }} fs-6 p-2">
                {{ $reservation->status_label }}
            </span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Reservation Info</h6>

            <div class="row g-2">
                <div class="col-4 text-muted small">Seat</div>
                <div class="col-8 fw-semibold">{{ $reservation->seat->seat_number }}</div>

                <div class="col-4 text-muted small">Area</div>
                <div class="col-8">{{ $reservation->seat->area->name }}</div>

                <div class="col-4 text-muted small">Date</div>
                <div class="col-8">{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('l, M d, Y') }}</div>

                <div class="col-4 text-muted small">Time</div>
                <div class="col-8">{{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}</div>

                <div class="col-4 text-muted small">Duration</div>
                <div class="col-8">{{ $reservation->duration_hours }} hour{{ $reservation->duration_hours > 1 ? 's' : '' }}</div>

                <div class="col-4 text-muted small">Status</div>
                <div class="col-8"><span class="status-badge {{ $reservation->status }}">{{ $reservation->status_label }}</span></div>

                <div class="col-4 text-muted small">Checked In</div>
                <div class="col-8">{{ $reservation->checked_in_at ? \Carbon\Carbon::parse($reservation->checked_in_at)->format('h:i A') : '-' }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-gear me-2 text-secondary"></i>Actions</h6>

            <div class="d-flex flex-column gap-2">
                @if($reservation->status === 'pending')
                    <form action="{{ route('reservations.check-in', $reservation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check2-circle me-2"></i>Check In Now
                        </button>
                    </form>
                    <small class="text-muted">Check in within 15 minutes of your start time.</small>
                @endif

                @if($reservation->status === 'checked_in')
                    <form action="{{ route('reservations.temporary-leave', $reservation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-box-arrow-right me-2"></i>Temporary Leave (15 min)
                        </button>
                    </form>
                    <small class="text-muted">You have 15 minutes to return or your seat will be released.</small>
                @endif

                @if($reservation->status === 'temporary_leave')
                    <form action="{{ route('reservations.return', $reservation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-left me-2"></i>Return from Leave
                        </button>
                    </form>
                    @php
                        $leaveStarted = \Carbon\Carbon::parse($reservation->temporary_leave_started_at);
                        $minutesLeft = 15 - $leaveStarted->diffInMinutes(now());
                    @endphp
                    <small class="text-{{ $minutesLeft > 5 ? 'warning' : 'danger' }}">
                        <i class="bi bi-clock me-1"></i>
                        {{ $minutesLeft > 0 ? $minutesLeft . ' minutes remaining' : 'Time is up! Please return now.' }}
                    </small>
                @endif

                @if(in_array($reservation->status, ['pending', 'confirmed']))
                    <form action="{{ route('reservations.cancel', $reservation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Cancel this reservation?')">
                            <i class="bi bi-x-circle me-2"></i>Cancel Reservation
                        </button>
                    </form>
                @endif

                @if(in_array($reservation->status, ['completed', 'cancelled', 'no_show']))
                    <div class="alert alert-secondary mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        This reservation is {{ $reservation->status }}.
                    </div>
                @endif
            </div>
        </div>

        @if($reservation->status === 'temporary_leave')
            <div class="card-custom p-4 mt-3 border border-warning">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-clock-history fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">Temporary Leave Active</h6>
                        <p class="small text-muted mb-0">
                            You left at {{ \Carbon\Carbon::parse($reservation->temporary_leave_started_at)->format('h:i A') }}.
                            Return within 15 minutes.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection