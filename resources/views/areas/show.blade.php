@extends('layouts.app')

@section('title', $area->name)

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('areas.index') }}" class="text-decoration-none">Areas</a></li>
                    <li class="breadcrumb-item active">{{ $area->name }}</li>
                </ol>
            </nav>
            <h1 class="mb-1">{{ $area->name }}</h1>
            <p class="text-muted small">{{ $area->description ?? 'Select a seat to book' }}</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-success fs-6 p-2">
                <i class="bi bi-check-circle me-1"></i>
                {{ $area->seats->where('is_occupied_now', false)->count() }} available
            </span>
        </div>
    </div>
</div>

<!-- Seat Grid -->
<div class="card-custom p-3 p-md-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <span class="small text-muted">Seat Map</span>
            <span class="badge bg-light text-dark ms-2">{{ $area->seats->count() }} seats</span>
        </div>
        <div class="d-flex gap-3 small">
            <span><span class="badge bg-success me-1">&nbsp;</span> Available</span>
            <span><span class="badge bg-danger me-1">&nbsp;</span> Occupied</span>
            <span><span class="badge bg-primary me-1">&nbsp;</span> Selected</span>
        </div>
    </div>

    <div class="seat-grid">
        @foreach($area->seats->sortBy('seat_number') as $seat)
            @php
                $isOccupied = $seat->is_occupied_now ?? false;
                $isAvailable = !$isOccupied;
            @endphp
            <button class="seat-item {{ $isAvailable ? 'available' : 'occupied' }} seat-btn" 
                    data-seat-id="{{ $seat->id }}"
                    data-seat-number="{{ $seat->seat_number }}"
                    data-area-id="{{ $area->id }}"
                    data-area-name="{{ $area->name }}"
                    {{ $isAvailable ? '' : 'disabled' }}
                    onclick="selectSeat(this)">
                <span class="seat-label">{{ $seat->seat_number }}</span>
            </button>
        @endforeach
    </div>
</div>

<!-- Booking Form -->
<div class="card-custom p-3 p-md-4 mt-3" id="bookingForm" style="display: none;">
    <h6 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2 text-primary"></i>Book Selected Seat</h6>

    <form id="reservationForm" action="{{ route('reservations.store') }}" method="POST">
        @csrf

        <input type="hidden" name="seat_id" id="selectedSeatId">
        <input type="hidden" name="area_id" id="selectedAreaId">

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Selected Seat</label>
                <div class="form-control bg-light" id="selectedSeatDisplay" style="cursor: default;">-</div>
            </div>

            <div class="col-md-4">
                <label for="reservation_date" class="form-label fw-semibold small">Date</label>
                <input type="date" class="form-control @error('reservation_date') is-invalid @enderror" 
                       id="reservation_date" name="reservation_date" 
                       min="{{ \Carbon\Carbon::now()->toDateString() }}"
                       max="{{ \Carbon\Carbon::now()->addDays(7)->toDateString() }}"
                       value="{{ old('reservation_date', \Carbon\Carbon::now()->toDateString()) }}" required>
                @error('reservation_date')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="start_time" class="form-label fw-semibold small">Start Time</label>
                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                       id="start_time" name="start_time" 
                       value="{{ old('start_time', '08:00') }}" required>
                @error('start_time')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="duration_hours" class="form-label fw-semibold small">Duration</label>
                <select class="form-select @error('duration_hours') is-invalid @enderror" 
                        id="duration_hours" name="duration_hours" required>
                    <option value="1">1 hour</option>
                    <option value="2" selected>2 hours</option>
                    <option value="3">3 hours</option>
                    <option value="4">4 hours</option>
                    <option value="6">6 hours</option>
                    <option value="8">8 hours</option>
                </select>
                @error('duration_hours')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold small">End Time</label>
                <div class="form-control bg-light" id="endTimeDisplay" style="cursor: default;">-</div>
            </div>

            <div class="col-12">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-2"></i>
                    Please check in within <strong>15 minutes</strong> of your start time by scanning your student ID at the turnstile.
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-circle me-2"></i>Confirm Booking
                </button>
                <button type="button" class="btn btn-outline-secondary ms-2" onclick="deselectSeat()">
                    Cancel
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Legend / Quick Stats -->
<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-4 small text-muted">
            <span><i class="bi bi-circle-fill text-success me-1" style="font-size: 0.6rem;"></i> Available: <strong>{{ $area->seats->where('is_occupied_now', false)->count() }}</strong></span>
            <span><i class="bi bi-circle-fill text-danger me-1" style="font-size: 0.6rem;"></i> Occupied: <strong>{{ $area->seats->where('is_occupied_now', true)->count() }}</strong></span>
            <span><i class="bi bi-clock me-1"></i> Showing current occupancy</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedSeatId = null;

function selectSeat(element) {
    // Deselect previous
    document.querySelectorAll('.seat-item.selected').forEach(el => {
        el.classList.remove('selected');
    });

    // Select new
    element.classList.add('selected');
    selectedSeatId = element.dataset.seatId;
    const seatNumber = element.dataset.seatNumber;
    const areaName = element.dataset.areaName;

    document.getElementById('selectedSeatId').value = selectedSeatId;
    document.getElementById('selectedAreaId').value = element.dataset.areaId;
    document.getElementById('selectedSeatDisplay').textContent = areaName + ' - ' + seatNumber;

    document.getElementById('bookingForm').style.display = 'block';

    // Scroll to form
    document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth', block: 'start' });

    updateEndTime();
}

function deselectSeat() {
    document.querySelectorAll('.seat-item.selected').forEach(el => {
        el.classList.remove('selected');
    });
    selectedSeatId = null;
    document.getElementById('bookingForm').style.display = 'none';
    document.getElementById('selectedSeatId').value = '';
    document.getElementById('selectedSeatDisplay').textContent = '-';
}

function updateEndTime() {
    const startTime = document.getElementById('start_time').value;
    const duration = parseInt(document.getElementById('duration_hours').value);

    if (startTime && duration) {
        const parts = startTime.split(':');
        let hours = parseInt(parts[0]) + duration;
        const minutes = parts[1] || '00';
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHour = hours > 12 ? hours - 12 : (hours === 0 ? 12 : hours);
        document.getElementById('endTimeDisplay').textContent = 
            displayHour + ':' + minutes + ' ' + ampm + ' (' + duration + ' hour' + (duration > 1 ? 's' : '') + ')';
    } else {
        document.getElementById('endTimeDisplay').textContent = '-';
    }
}

document.getElementById('start_time').addEventListener('change', updateEndTime);
document.getElementById('duration_hours').addEventListener('change', updateEndTime);

// Auto-select first available seat? No, let user choose.

// Set default date to today
document.getElementById('reservation_date').value = '{{ \Carbon\Carbon::now()->toDateString() }}';

// Initialize end time
updateEndTime();
</script>
@endpush
@endsection