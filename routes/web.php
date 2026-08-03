<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TurnstileController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Areas
    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/{id}', [AreaController::class, 'show'])->name('areas.show');

    // Seats - API for AJAX
    Route::get('/seats/available', [SeatController::class, 'getAvailableSeats'])->name('seats.available');

    // Reservations
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{id}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/reservations/{id}/check-in', [ReservationController::class, 'checkIn'])->name('reservations.check-in');
    Route::post('/reservations/{id}/temporary-leave', [ReservationController::class, 'temporaryLeave'])->name('reservations.temporary-leave');
    Route::post('/reservations/{id}/return', [ReservationController::class, 'returnFromLeave'])->name('reservations.return');
    Route::post('/reservations/{id}/check-out', [ReservationController::class, 'checkOut'])->name('reservations.check-out');

    // Turnstile simulator
    Route::get('/turnstile/simulator', [TurnstileController::class, 'showSimulator'])->name('turnstile.simulator');
    Route::post('/turnstile/simulate', [TurnstileController::class, 'simulateScan'])->name('turnstile.simulate');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/reservations', [AdminController::class, 'allReservations'])->name('reservations');
    });
});