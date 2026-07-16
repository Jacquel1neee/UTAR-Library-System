<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'total_seats',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function getAvailableSeatsCountAttribute()
    {
        return $this->seats()->where('is_active', true)->count();
    }

    public function getOccupiedSeatsCountAttribute()
    {
        // Get currently occupied seats (checked in or temporary leave)
        $now = now();
        return $this->seats()
            ->whereHas('reservations', function ($query) use ($now) {
                $query->where('reservation_date', $now->toDateString())
                    ->where('end_time', '>=', $now->toTimeString())
                    ->whereIn('status', ['checked_in', 'temporary_leave']);
            })
            ->count();
    }
}