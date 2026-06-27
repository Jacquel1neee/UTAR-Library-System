@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">Admin Dashboard</h1>
            <p class="text-muted small">Library occupancy overview and statistics</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-primary p-2">
                <i class="bi bi-shield-lock me-1"></i> Admin
            </span>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Total Users</p>
                    <h3 class="fw-bold mb-0">{{ $totalUsers }}</h3>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left-color: var(--success);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Total Reservations</p>
                    <h3 class="fw-bold mb-0">{{ $totalReservations }}</h3>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left-color: var(--warning);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Active Now</p>
                    <h3 class="fw-bold mb-0">{{ $activeReservations }}</h3>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left-color: var(--secondary);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="small text-muted mb-0">Today's Bookings</p>
                    <h3 class="fw-bold mb-0">{{ $todayReservations }}</h3>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Area Occupancy -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-layers me-2 text-primary"></i>Area Occupancy</h6>
            <div class="row g-3">
                @foreach($areas as $area)
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle me-2" style="width: 12px; height: 12px; background: {{ $area->color }};"></div>
                                    <span class="fw-semibold">{{ $area->name }}</span>
                                </div>
                                <span class="small text-muted">{{ $area->code }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Available: <strong class="text-success">{{ $area->available_count }}</strong></span>
                                <span>Occupied: <strong class="text-danger">{{ $area->occupied_count }}</strong></span>
                                <span>Total: <strong>{{ $area->seats_count }}</strong></span>
                            </div>
                            <div class="progress mt-2" style="height: 6px;">
                                @php
                                    $pct = $area->seats_count > 0 ? round(($area->occupied_count / $area->seats_count) * 100) : 0;
                                @endphp
                                <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%; background: {{ $area->color }};" 
                                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Recent Reservations -->
<div class="row g-3">
    <div class="col-12">
        <div class="card-custom p-0 overflow-hidden">
            <div class="p-3 border-bottom bg-light">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Recent Reservations</h6>
            </div>
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
                        @forelse($recentReservations as $res)
                            <tr>
                                <td class="py-2 px-4">{{ $res->user->name }}</td>
                                <td class="py-2">{{ $res->seat->seat_number }}</td>
                                <td class="py-2">{{ $res->seat->area->name }}</td>
                                <td class="py-2">{{ \Carbon\Carbon::parse($res->reservation_date)->format('M d') }}</td>
                                <td class="py-2">{{ \Carbon\Carbon::parse($res->start_time)->format('h:i A') }}</td>
                                <td class="py-2"><span class="status-badge {{ $res->status }}">{{ $res->status_label }}</span></td>
                                <td class="py-2 text-end pe-4 text-muted small">{{ \Carbon\Carbon::parse($res->created_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No reservations yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection