<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Costume extends Model
{
    use HasFactory, SoftDeletes;

    public const SIZES = ['S', 'M', 'L', 'XL'];

    public const GENDERS = [
        'male' => 'Pria',
        'female' => 'Wanita',
        'unisex' => 'Unisex',
    ];

    protected $fillable = [
        'tenant_id',
        'category_id',
        'character_name',
        'description',
        'accessories',
        'gender',
        'city',
        'price_per_day',
        'deposit',
        'is_popular',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'integer',
            'deposit' => 'integer',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CostumeImage::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(CostumeSize::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(AvailabilityBlock::class);
    }

    public function reviews(): HasManyThrough
    {
        // Rating kostum hanya berasal dari ulasan customer terhadap tenant.
        return $this->hasManyThrough(Review::class, Booking::class, 'costume_id', 'booking_id', 'id', 'id')
            ->where('reviews.target', Review::TARGET_TENANT);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->active()->whereHas('tenant', fn (Builder $q) => $q->where('status', Tenant::STATUS_ACTIVE));
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['q'] ?? null, function (Builder $q, string $keyword) {
                $q->where(function (Builder $inner) use ($keyword) {
                    $inner->where('character_name', 'like', '%'.$keyword.'%')
                        ->orWhere('description', 'like', '%'.$keyword.'%')
                        ->orWhere('accessories', 'like', '%'.$keyword.'%');
                });
            })
            ->when($filters['kota'] ?? null, fn (Builder $q, string $city) => $q->where('city', $city))
            ->when($filters['gender'] ?? null, function (Builder $q, string $gender) {
                $q->where(function (Builder $inner) use ($gender) {
                    $inner->where('gender', $gender)->orWhere('gender', 'unisex');
                });
            })
            ->when($filters['kategori'] ?? null, fn (Builder $q, $category) => $q->where('category_id', $category))
            ->when($filters['ukuran'] ?? null, function (Builder $q, string $size) {
                $q->whereHas('sizes', fn (Builder $inner) => $inner->where('size', $size)->where('stock', '>', 0));
            });
    }

    public function primaryImageUrl(): string
    {
        $path = $this->relationLoaded('images')
            ? $this->images->first()?->path
            : $this->images()->value('path');

        return self::imageUrl($path);
    }

    public static function imageUrl(?string $path): string
    {
        if (! $path) {
            return asset('images/placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    public function averageRating(): float
    {
        $average = $this->reviews()->avg('rating');

        return $average ? round((float) $average, 1) : 0.0;
    }

    public function genderLabel(): string
    {
        return self::GENDERS[$this->gender] ?? $this->gender;
    }

    public function hasAvailableStock(): bool
    {
        return $this->sizes->contains(fn (CostumeSize $size) => $size->stock > 0);
    }
}
