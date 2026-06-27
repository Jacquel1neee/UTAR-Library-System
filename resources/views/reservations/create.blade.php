@extends('layouts.app')

@section('title', 'Book a Seat')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">Book a Seat</h1>
            <p class="text-muted small">Select an area first, then choose a seat</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('areas.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Areas
            </a>
        </div>
    </div>
</div>

<div class="row g-3">
    @php
        $areas = \App\Models\Area::withCount(['seats' => function($q) { $q->where('is_active', true); }])->get();
        $now = \Carbon\Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();

        foreach ($areas as $area) {
            $occupied = \App\Models\Reservation::whereHas('seat', function($q) use ($area) {
                $q->where('area_id', $area->id);
            })
                ->where('reservation_date', $today)
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime)
                ->whereIn('status', ['checked_in', 'temporary_leave'])
                ->count();
            $area->available_count = $area->seats_count - $occupied;
        }
    @endphp

    @foreach($areas as $area)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('areas.show', $area->id) }}" class="area-card card-custom p-4 text-decoration-none text-reset d-block">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2 area-color-dot" data-bg="{{ $area->color }}" style="width: 14px; height: 14px;"></div>
                            <h6 class="fw-bold mb-0">{{ $area->name }}</h6>
                        </div>
                        <p class="small text-muted mb-2">{{ $area->code }}</p>
                    </div>
                    <span class="badge {{ $area->available_count > 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ $area->available_count > 0 ? $area->available_count . ' free' : 'Full' }}
                    </span>
                </div>
                <div class="progress" style="height: 4px;">
                    @php
                        $pct = $area->seats_count > 0 ? round((($area->seats_count - $area->available_count) / $area->seats_count) * 100) : 0;
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: 0%;" data-width="{{ $pct }}" data-bg="{{ $area->color }}"></div>
                </div>
                <div class="mt-2 small text-muted">
                    {{ $area->available_count }} of {{ $area->seats_count }} available
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection