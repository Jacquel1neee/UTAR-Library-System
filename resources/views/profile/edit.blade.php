@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="mb-1">My Profile</h1>
            <p class="text-muted small">Manage your account information and password.</p>
        </div>
        <div class="col-auto">
            <span class="badge bg-primary p-2"><i class="bi bi-person-circle me-1"></i>{{ ucfirst($user->role) }}</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-custom p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-vcard me-2 text-primary"></i>Personal Information</h6>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label fw-semibold small">Full Name</label>
                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold small">Email Address</label>
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="student_id" class="form-label fw-semibold small">Student ID</label>
                        <input id="student_id" name="student_id" type="text" class="form-control @error('student_id') is-invalid @enderror" value="{{ old('student_id', $user->student_id) }}" required>
                        @error('student_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="phone_number" class="form-label fw-semibold small">Phone Number</label>
                        <input id="phone_number" name="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}">
                        @error('phone_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-primary"></i>Change Password</h6>
                <p class="small text-muted">Leave these fields empty if you do not want to change your password.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="current_password" class="form-label fw-semibold small">Current Password</label>
                        <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror">
                        @error('current_password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold small">New Password</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-semibold small">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom mt-4"><i class="bi bi-save me-2"></i>Save Changes</button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-custom p-4">
            <div class="text-center mb-3">
                <div class="avatar-circle mx-auto mb-3" style="width: 72px; height: 72px; background: var(--primary); font-size: 1.5rem;">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
            </div>
            <div class="border-top pt-3">
                <div class="d-flex justify-content-between mb-2"><span class="text-muted small">Account type</span><span class="small fw-semibold">{{ ucfirst($user->role) }}</span></div>
                <div class="d-flex justify-content-between"><span class="text-muted small">Member since</span><span class="small fw-semibold">{{ $user->created_at?->format('M Y') ?? '—' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
