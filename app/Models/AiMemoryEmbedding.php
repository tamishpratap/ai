<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMemoryEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'memory_id',
        'model',
        'dimensions',
        'vector',
    ];

    protected function casts(): array
    {
        return [
            'vector' => 'array',
            'dimensions' => 'integer',
        ];
    }

    public function memory(): BelongsTo
    {
        return $this->belongsTo(AiMemory::class, 'memory_id');
    }
}
