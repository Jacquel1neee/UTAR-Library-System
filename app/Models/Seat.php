<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'seat_number',
        'row_label',
        'col_position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isAvailableFor(string $date, string $startTime, string $endTime): bool
    {
        return !$this->reservations()
            ->where('reservation_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'no_show')
            ->where('status', '!=', 'completed')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();
    }
}