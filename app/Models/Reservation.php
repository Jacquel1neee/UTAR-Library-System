<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seat_id',
        'reservation_date',
        'start_time',
        'end_time',
        'duration_hours',
        'status',
        'checked_in_at',
        'temporary_leave_started_at',
        'temporary_leave_ended_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'checked_in_at' => 'datetime',
        'temporary_leave_started_at' => 'datetime',
        'temporary_leave_ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'checked_in' => 'Checked In',
            'temporary_leave' => 'On Temporary Leave',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'checked_in' => 'green',
            'temporary_leave' => 'orange',
            'completed' => 'gray',
            'cancelled' => 'red',
            'no_show' => 'red',
        ][$this->status] ?? 'gray';
    }
}