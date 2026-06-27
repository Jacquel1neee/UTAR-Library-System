@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">Dashboard</h1>
            <p class="text-muted small">Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('areas.index') }}" class="btn btn-primary-custom">
                <i class="bi bi-plus-circle me-2"></i>Book a Seat
            </a>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    @php
        $totalSeats = 0;
        $totalAvailable = 0;
        $totalOccupied = 0;
        foreach ($areas as $area) {
            $totalSeats += $area->seats_count;
            $totalAvailable += $area->available_count;
            $totalOccupied += $area->occupied_count;
        }
    @endphp

    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Total Seats</p>
                    <h3 class="fw-bold mb-0">{{ $totalSeats }}</h3>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-chair"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left-color: var(--success);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Available Now</p>
                    <h3 class="fw-bold mb-0 text-success">{{ $totalAvailable }}</h3>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left-color: var(--danger);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Occupied</p>
                    <h3 class="fw-bold mb-0 text-danger">{{ $totalOccupied }}</h3>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left-color: var(--warning);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Utilization</p>
                    <h3 class="fw-bold mb-0">
                        @php
                            $utilization = $totalSeats > 0 ? round(($totalOccupied / $totalSeats) * 100) : 0;
                        @endphp
                        {{ $utilization }}%
                    </h3>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Areas Overview -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-layers me-2"></i>Areas Overview</h5>
            <a href="{{ route('areas.index') }}" class="small text-primary text-decoration-none">View All →</a>
        </div>
    </div>

    @foreach($areas as $area)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('areas.show', $area->id) }}" class="area-card card-custom p-3 text-decoration-none text-reset d-block">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle me-2 area-color-dot" data-bg="{{ $area->color }}" style="width: 12px; height: 12px;"></div>
                        <h6 class="fw-bold mb-0">{{ $area->name }}</h6>
                    </div>
                    <span class="badge bg-light text-dark">{{ $area->code }}</span>
                </div>
                <div class="row g-1 small text-muted">
                    <div class="col-4">
                        <span class="fw-semibold text-dark">{{ $area->seats_count }}</span> total
                    </div>
                    <div class="col-4">
                        <span class="fw-semibold text-success">{{ $area->available_count }}</span> available
                    </div>
                    <div class="col-4">
                        <span class="fw-semibold text-danger">{{ $area->occupied_count }}</span> occupied
                    </div>
                </div>
                <div class="progress mt-2" style="height: 4px;">
                    @php
                        $pct = $area->seats_count > 0 ? round(($area->occupied_count / $area->seats_count) * 100) : 0;
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: 0%;" data-width="{{ $pct }}" data-bg="{{ $area->color }}" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Active & Upcoming Reservations -->
<div class="row g-3">
    @if(!empty($activeReservations) && count($activeReservations) > 0)
        <div class="col-md-6">
            <div class="card-custom p-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Active Reservations</h6>
                @foreach($activeReservations as $res)
                    <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                        <div>
                            <span class="fw-semibold small">{{ $res->seat->seat_number }}</span>
                            <span class="text-muted small"> · {{ $res->seat->area->name }}</span>
                            <br>
                            <span class="small text-muted">{{ \Carbon\Carbon::parse($res->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($res->end_time)->format('h:i A') }}</span>
                        </div>
                        <span class="status-badge {{ $res->status }}">{{ $res->status_label }}</span>
                    </div>
                @endforeach
                <a href="{{ route('reservations.index') }}" class="small text-primary text-decoration-none mt-2 d-block">View all →</a>
            </div>
        </div>
    @endif

    @if(!empty($upcomingReservations) && count($upcomingReservations) > 0)
        <div class="col-md-6">
            <div class="card-custom p-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2 text-secondary"></i>Upcoming Reservations</h6>
                @foreach($upcomingReservations as $res)
                    <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                        <div>
                            <span class="fw-semibold small">{{ $res->seat->seat_number }}</span>
                            <span class="text-muted small"> · {{ $res->seat->area->name }}</span>
                            <br>
                            <span class="small text-muted">{{ \Carbon\Carbon::parse($res->reservation_date)->format('M d') }}, {{ \Carbon\Carbon::parse($res->start_time)->format('h:i A') }}</span>
                        </div>
                        <span class="status-badge {{ $res->status }}">{{ $res->status_label }}</span>
                    </div>
                @endforeach
                <a href="{{ route('reservations.index') }}" class="small text-primary text-decoration-none mt-2 d-block">View all →</a>
            </div>
        </div>
    @endif

    @if((empty($activeReservations) || count($activeReservations) == 0) && (empty($upcomingReservations) || count($upcomingReservations) == 0))
        <div class="col-12">
            <div class="card-custom p-5 text-center">
                <i class="bi bi-calendar-plus fs-1 text-muted"></i>
                <h5 class="mt-3">No Reservations Yet</h5>
                <p class="text-muted small">Book your first seat now!</p>
                <a href="{{ route('areas.index') }}" class="btn btn-primary-custom">Book a Seat</a>
            </div>
        </div>
    @endif
</div>
@endsection