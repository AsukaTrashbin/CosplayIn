<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_WAITING_CONFIRMATION = 'waiting_confirmation';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WAITING_PAYMENT = 'waiting_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_READY_TO_PROCESS = 'ready_to_process';

    public const STATUS_RENTED = 'rented';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_WAITING_CONFIRMATION,
        self::STATUS_REJECTED,
        self::STATUS_WAITING_PAYMENT,
        self::STATUS_PAID,
        self::STATUS_FAILED,
        self::STATUS_READY_TO_PROCESS,
        self::STATUS_RENTED,
        self::STATUS_RETURNED,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'booking_code',
        'customer_id',
        'tenant_id',
        'costume_id',
        'size',
        'booker_name',
        'identity_number',
        'phone',
        'address',
        'rental_date',
        'return_date',
        'duration_days',
        'rental_price',
        'deposit',
        'shipping_fee',
        'total',
        'status',
        'shipping_method',
        'ktp_path',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'rental_date' => 'date',
            'return_date' => 'date',
            'duration_days' => 'integer',
            'rental_price' => 'integer',
            'deposit' => 'integer',
            'shipping_fee' => 'integer',
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (! $booking->booking_code) {
                $booking->booking_code = 'CPI-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function costume(): BelongsTo
    {
        return $this->belongsTo(Costume::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function returnItem(): HasOne
    {
        return $this->hasOne(ReturnItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_WAITING_CONFIRMATION => 'Menunggu Konfirmasi Tenant',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_WAITING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Dibayar / Siap Diproses',
            self::STATUS_FAILED => 'Pembayaran Gagal',
            self::STATUS_READY_TO_PROCESS => 'Siap Diproses',
            self::STATUS_RENTED => 'Sedang Disewa',
            self::STATUS_RETURNED => 'Menunggu Verifikasi Pengembalian',
            self::STATUS_COMPLETED => 'Selesai',
            default => $this->status,
        };
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_WAITING_PAYMENT,
            self::STATUS_PAID,
            self::STATUS_READY_TO_PROCESS,
            self::STATUS_RENTED,
            self::STATUS_RETURNED,
        ], true);
    }

    public function canStartReturn(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_READY_TO_PROCESS,
            self::STATUS_RENTED,
        ], true) && $this->return_date->lte(now()->startOfDay());
    }

    public function canBeReviewedBy(User $user): bool
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        if ($this->customer_id === $user->id || ($user->tenant && $this->tenant_id === $user->tenant->id)) {
            return ! $this->reviews()->where('reviewer_id', $user->id)->exists();
        }

        return false;
    }

    public function reviewTargetFor(User $user): ?string
    {
        if ($this->customer_id === $user->id) {
            return Review::TARGET_TENANT;
        }

        if ($user->tenant && $this->tenant_id === $user->tenant->id) {
            return Review::TARGET_CUSTOMER;
        }

        return null;
    }
}
