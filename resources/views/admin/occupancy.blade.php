@extends('layouts.app')

@section('title', 'Occupancy Reports')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">Occupancy Statistics</h1>
            <p class="text-muted small">Review daily seat usage and download reports</p>
        </div>
        <div class="col-auto"><a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a></div>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <form method="GET" action="{{ route('admin.occupancy') }}" class="row g-3 align-items-end">
        <div class="col-md-4"><label for="from" class="form-label small fw-semibold">From</label><input type="date" id="from" name="from" class="form-control" value="{{ $from->toDateString() }}" required></div>
        <div class="col-md-4"><label for="to" class="form-label small fw-semibold">To</label><input type="date" id="to" name="to" class="form-control" value="{{ $to->toDateString() }}" required></div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary-custom flex-grow-1" type="submit"><i class="bi bi-filter me-1"></i>Apply</button><a class="btn btn-outline-success" href="{{ route('admin.occupancy.export', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}" title="Download CSV"><i class="bi bi-download"></i></a></div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat-card"><p class="small text-muted mb-0">Average Occupancy</p><h3 class="fw-bold mb-0">{{ number_format($averageOccupancy, 1) }}%</h3></div></div>
    <div class="col-md-4"><div class="stat-card" style="border-left-color: var(--danger);"><p class="small text-muted mb-0">Peak Occupied Seats</p><h3 class="fw-bold mb-0">{{ $peakOccupied }}</h3></div></div>
    <div class="col-md-4"><div class="stat-card" style="border-left-color: var(--secondary);"><p class="small text-muted mb-0">Reporting Days</p><h3 class="fw-bold mb-0">{{ $report->count() }}</h3></div></div>
</div>

<div class="card-custom p-0 overflow-hidden">
    <div class="p-3 border-bottom bg-light"><h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Daily Occupancy</h6></div>
    <div class="table-responsive"><table class="table table-hover mb-0 align-middle">
        <thead><tr><th class="py-2 px-4">Date</th><th class="py-2">Active Seats</th><th class="py-2">Occupied Seats</th><th class="py-2">Reservations</th><th class="py-2 px-4">Occupancy</th></tr></thead>
        <tbody>
            @foreach($report as $row)
                <tr><td class="py-2 px-4">{{ $row['date']->format('D, M d, Y') }}</td><td class="py-2">{{ $row['active_seats'] }}</td><td class="py-2">{{ $row['occupied_seats'] }}</td><td class="py-2">{{ $row['reservations'] }}</td><td class="py-2 px-4" style="min-width: 220px"><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height: 8px"><div class="progress-bar bg-primary" style="width: {{ min($row['occupancy_percentage'], 100) }}%"></div></div><small class="fw-semibold">{{ number_format($row['occupancy_percentage'], 1) }}%</small></div></td></tr>
            @endforeach
        </tbody>
    </table></div>
</div>
@endsection
