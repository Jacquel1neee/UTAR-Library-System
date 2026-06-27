@extends('layouts.app')

@section('title', 'Areas')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">Library Areas</h1>
            <p class="text-muted small">Select an area to view and book available seats</p>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach($areas as $area)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('areas.show', $area->id) }}" class="area-card card-custom p-4 text-decoration-none text-reset d-block">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle me-2 area-color-dot" data-bg="{{ $area->color }}" style="width: 16px; height: 16px;"></div>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $area->name }}</h6>
                            <span class="small text-muted">{{ $area->code }}</span>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>

                @if($area->description)
                    <p class="small text-muted mb-3">{{ $area->description }}</p>
                @endif

                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded-3">
                            <span class="d-block fw-bold">{{ $area->seats_count }}</span>
                            <span class="small text-muted">Total</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-success bg-opacity-10 rounded-3">
                            <span class="d-block fw-bold text-success">{{ $area->available_count }}</span>
                            <span class="small text-muted">Available</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-danger bg-opacity-10 rounded-3">
                            <span class="d-block fw-bold text-danger">{{ $area->occupied_count }}</span>
                            <span class="small text-muted">Occupied</span>
                        </div>
                    </div>
                </div>

                <div class="progress mt-3" style="height: 6px;">
                    @php
                        $pct = $area->seats_count > 0 ? round(($area->occupied_count / $area->seats_count) * 100) : 0;
                    @endphp
                    <div class="progress-bar" role="progressbar" style="width: 0%;" data-width="{{ $pct }}" data-bg="{{ $area->color }}" 
                         aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="mt-3">
                    <span class="badge {{ $area->available_count > 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ $area->available_count > 0 ? $area->available_count . ' seats available' : 'Fully occupied' }}
                    </span>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection