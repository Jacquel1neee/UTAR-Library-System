@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-6 col-lg-5">
        <div class="card-custom p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-person-plus fs-2 text-primary"></i>
                </div>
                <h4 class="mt-3 fw-bold">Create Account</h4>
                <p class="text-muted small">Register to book library seats</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label fw-semibold small">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="John Doe" required>
                        </div>
                        @error('name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label fw-semibold small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="your@utar.edu.my" required>
                        </div>
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="student_id" class="form-label fw-semibold small">Student ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-card-text text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 @error('student_id') is-invalid @enderror" 
                                   id="student_id" name="student_id" value="{{ old('student_id') }}" 
                                   placeholder="2204287" required>
                        </div>
                        @error('student_id')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="phone_number" class="form-label fw-semibold small">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-phone text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 @error('phone_number') is-invalid @enderror" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number') }}" 
                                   placeholder="0123456789">
                        </div>
                        @error('phone_number')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label fw-semibold small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="password_confirmation" class="form-label fw-semibold small">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-check-circle text-muted"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100 mt-4">
                    <i class="bi bi-person-plus me-2"></i>Register
                </button>
            </form>

            <hr class="my-4">

            <p class="text-center small text-muted mb-0">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Login</a>
            </p>
        </div>
    </div>
</div>
@endsection