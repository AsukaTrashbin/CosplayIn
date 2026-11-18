<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    use HasFactory;

    protected $table = 'returns';

    public const CONDITION_GOOD = 'good';

    public const CONDITION_DAMAGED_LOST = 'damaged_lost';

    public const METHOD_COURIER = 'courier';

    public const METHOD_COD = 'cod';

    protected $fillable = [
        'booking_id',
        'actual_return_date',
        'condition',
        'method',
        'tracking_number',
        'cod_location',
        'cod_time',
        'days_late',
        'late_fee',
        'damage_claim',
        'deposit_deduction',
        'deposit_refunded',
        'tenant_note',
    ];

    protected function casts(): array
    {
        return [
            'actual_return_date' => 'date',
            'days_late' => 'integer',
            'late_fee' => 'integer',
            'damage_claim' => 'integer',
            'deposit_deduction' => 'integer',
            'deposit_refunded' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function conditionLabel(): string
    {
        return $this->condition === self::CONDITION_DAMAGED_LOST
            ? 'Rusak / Hilang'
            : 'Baik dan Lengkap';
    }

    public function methodLabel(): string
    {
        return $this->method === self::METHOD_COURIER ? 'Kurir / Ekspedisi' : 'COD / Ketemu Langsung';
    }
}
