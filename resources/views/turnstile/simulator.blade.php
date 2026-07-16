@extends('layouts.app')

@section('title', 'Turnstile Simulator')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">Turnstile Simulator</h1>
            <p class="text-muted small">Simulate student ID scan in/out for testing</p>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Simulate Scan</h6>

            <form id="scanForm">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="user_id" class="form-label fw-semibold small">Student</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Select a student...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->student_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="event_type" class="form-label fw-semibold small">Simulated Action</label>
                        <select class="form-select" id="event_type" name="event_type" required>
                            <option value="entry">Scan In / Auto Check In</option>
                            <option value="exit">Scan Out / Auto Check Out</option>
                            <option value="temporary_leave">Press Leave Button / Temporary Leave</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="bi bi-sim me-2"></i>Simulate Scan
                        </button>
                    </div>
                </div>
            </form>

            <div id="scanResult" class="mt-3" style="display: none;">
                <div class="alert" id="resultAlert"></div>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-secondary"></i>How Auto-Detect Works</h6>
            
            <div class="mb-3 p-3 bg-light rounded-3">
                <h6 class="fw-bold small text-primary"><i class="bi bi-box-arrow-in-right me-1"></i> Scan In / Auto Check In</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Checks if you already have an active reservation</li>
                    <li>If on temporary leave, returns you automatically</li>
                    <li>Auto-detects your nearest pending reservation</li>
                    <li>Checks in within 15 min before to 60 min after start time</li>
                </ul>
            </div>

            <div class="mb-3 p-3 bg-light rounded-3">
                <h6 class="fw-bold small text-warning"><i class="bi bi-box-arrow-right me-1"></i> Press Leave Button / Temporary Leave</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Marks the current checked-in reservation as temporary leave</li>
                    <li>Gives the student 15 minutes to return</li>
                    <li>Does not fully release the seat yet</li>
                </ul>
            </div>

            <div class="p-3 bg-light rounded-3">
                <h6 class="fw-bold small text-danger"><i class="bi bi-box-arrow-right me-1"></i> Scan Out / Auto Check Out</h6>
                <ul class="small text-muted ps-3 mb-0">
                    <li>Releases all your active reservations</li>
                    <li>Frees up seats for other students</li>
                </ul>
            </div>

            <div class="mt-3 alert alert-info small mb-0">
                <i class="bi bi-lightbulb me-2"></i>
                In production, this will be replaced with real turnstile API integration.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#scanForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: '{{ route("turnstile.simulate") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#scanResult').show();
                const alert = $('#resultAlert');
                alert.removeClass('alert-success alert-danger')
                     .addClass('alert-success')
                     .html('<i class="bi bi-check-circle me-2"></i> ' + response.message);
                
                // Refresh page after 2 seconds
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error: function(xhr) {
                $('#scanResult').show();
                const alert = $('#resultAlert');
                alert.removeClass('alert-success alert-danger')
                     .addClass('alert-danger');

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert.html('<i class="bi bi-exclamation-circle me-2"></i> ' + xhr.responseJSON.message);
                } else {
                    alert.html('<i class="bi bi-exclamation-circle me-2"></i> Something went wrong.');
                }
            }
        });
    });
});
</script>
@endpush
@endsection