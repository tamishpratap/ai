<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLearnedPreference extends Model
{
    use HasFactory;

    protected $table = 'ai_learned_preferences';

    protected $fillable = [
        'member_id',
        'category',
        'key',
        'value',
        'confidence_score',
        'occurrence_count',
    ];

    protected $casts = [
        'value'            => 'array',
        'confidence_score' => 'float',
        'occurrence_count' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }
}
