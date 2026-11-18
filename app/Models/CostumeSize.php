<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostumeSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'costume_id',
        'size',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }

    public function costume(): BelongsTo
    {
        return $this->belongsTo(Costume::class);
    }
}
