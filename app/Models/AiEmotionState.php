<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEmotionState extends Model
{
    use HasFactory;

    protected $table = 'ai_emotion_states';

    protected $fillable = [
        'member_id',
        'conversation_id',
        'primary_state',
        'secondary_state',
        'intensity',
        'confidence',
        'detected_sentiment',
        'reason',
        'context_summary',
        'metadata',
    ];

    protected $casts = [
        'intensity' => 'float',
        'confidence' => 'float',
        'metadata' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
