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
    <div class="col-md-8">
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
                        <label for="event_type" class="form-label fw-semibold small">Event Type</label>
                        <select class="form-select" id="event_type" name="event_type" required>
                            <option value="entry">Entry (Scan In)</option>
                            <option value="exit">Exit (Scan Out)</option>
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

    <div class="col-md-4">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-secondary"></i>How it works</h6>
            <ul class="small text-muted ps-3">
                <li class="mb-2">Select a student and scan type</li>
                <li class="mb-2"><strong>Entry:</strong> Checks in pending reservations within 15min of start time</li>
                <li class="mb-2"><strong>Entry:</strong> Returns from temporary leave (if within 15min)</li>
                <li class="mb-2"><strong>Exit:</strong> Releases any active reservations (checked_in or temporary_leave)</li>
                <li class="mb-2">All events are logged in the turnstile_events table</li>
            </ul>
            <div class="alert alert-info small mb-0">
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