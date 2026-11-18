<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailabilityBlock extends Model
{
    use HasFactory;

    public const TYPE_BOOKED = 'booked';

    public const TYPE_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'costume_id',
        'size',
        'date',
        'type',
        'notes',
        'booking_id',
    ];

    protected function casts(): array
    {
        return [
            // Format eksplisit agar perbandingan tanggal konsisten di MySQL dan SQLite.
            'date' => 'date:Y-m-d',
        ];
    }

    public function costume(): BelongsTo
    {
        return $this->belongsTo(Costume::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
