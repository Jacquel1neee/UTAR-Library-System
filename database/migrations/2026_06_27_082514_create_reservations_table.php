<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('seat_id')->constrained()->onDelete('cascade');
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_hours');
            $table->enum('status', [
                'pending',
                'confirmed',
                'checked_in',
                'temporary_leave',
                'completed',
                'cancelled',
                'no_show'
            ])->default('pending');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('temporary_leave_started_at')->nullable();
            $table->timestamp('temporary_leave_ended_at')->nullable();
            $table->timestamps();

            // Prevent double booking for same seat at same time
            $table->unique(['seat_id', 'reservation_date', 'start_time', 'end_time'], 'unique_seat_booking');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};