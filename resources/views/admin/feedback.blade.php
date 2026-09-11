@extends('layouts.app')

@section('title', 'User Feedback')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">User Feedback</h1>
            <p class="text-muted small">Review comments and ratings submitted by library users</p>
        </div>
        <div class="col-auto"><a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a></div>
    </div>
</div>

<div class="card-custom p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th class="py-2 px-4">User</th><th class="py-2">Reservation</th><th class="py-2">Rating</th><th class="py-2">Comment</th><th class="py-2 text-end pe-4">Submitted</th></tr></thead>
            <tbody>
                @forelse($feedback as $item)
                    <tr>
                        <td class="py-2 px-4">{{ $item->user->name }}<br><small class="text-muted">{{ $item->user->student_id }}</small></td>
                        <td class="py-2">@if($item->reservation)#{{ $item->reservation->id }} · {{ $item->reservation->seat->seat_number }}@else<span class="text-muted">General feedback</span>@endif</td>
                        <td class="py-2 text-warning">{{ str_repeat('★', $item->rating) }}<span class="text-muted">{{ str_repeat('★', 5 - $item->rating) }}</span></td>
                        <td class="py-2">{{ $item->comment ?: '—' }}</td>
                        <td class="py-2 text-end pe-4 text-muted small">{{ $item->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No feedback submitted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $feedback->links() }}</div>
@endsection
