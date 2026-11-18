<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostumeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'costume_id',
        'path',
    ];

    public function costume(): BelongsTo
    {
        return $this->belongsTo(Costume::class);
    }

    public function url(): string
    {
        return Costume::imageUrl($this->path);
    }
}
