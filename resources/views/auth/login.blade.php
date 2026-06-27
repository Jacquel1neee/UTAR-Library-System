@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card-custom p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bi bi-book fs-2 text-primary"></i>
                </div>
                <h4 class="mt-3 fw-bold">Welcome Back</h4>
                <p class="text-muted small">Login to book your library seat</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-envelope text-muted"></i>
                        </span>
                        <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" 
                               placeholder="your@utar.edu.my" required autofocus>
                    </div>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
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

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="#" class="small text-primary text-decoration-none">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </form>

            <hr class="my-4">

            <p class="text-center small text-muted mb-0">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Register</a>
            </p>

            <div class="mt-3 p-3 bg-light rounded-3">
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Demo: <strong>john@utar.edu.my</strong> / <strong>password123</strong>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection