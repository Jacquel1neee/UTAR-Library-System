@extends('layouts.app')

@section('title', 'My Reservations')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">My Reservations</h1>
            <p class="text-muted small">View and manage all your seat bookings</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('areas.index') }}" class="btn btn-primary-custom">
                <i class="bi bi-plus-circle me-2"></i>New Booking
            </a>
        </div>
    </div>
</div>

<div class="card-custom p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="py-3 px-4">Seat</th>
                    <th class="py-3">Area</th>
                    <th class="py-3">Date</th>
                    <th class="py-3">Time</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $res)
                    <tr>
                        <td class="py-3 px-4 fw-semibold">{{ $res->seat->seat_number }}</td>
                        <td class="py-3">{{ $res->seat->area->name }}</td>
                        <td class="py-3">{{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}</td>
                        <td class="py-3">{{ \Carbon\Carbon::parse($res->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($res->end_time)->format('h:i A') }}</td>
                        <td class="py-3">
                            <span class="status-badge {{ $res->status }}">{{ $res->status_label }}</span>
                        </td>
                        <td class="py-3 text-end pe-4">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('reservations.show', $res->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(in_array($res->status, ['pending', 'confirmed']))
                                    <form action="{{ route('reservations.cancel', $res->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                onclick="return confirm('Cancel this reservation?')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar2-x fs-2 d-block mb-2"></i>
                            No reservations found
                            <br>
                            <a href="{{ route('areas.index') }}" class="btn btn-sm btn-primary-custom mt-2">Book a seat</a>
                        </td>
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