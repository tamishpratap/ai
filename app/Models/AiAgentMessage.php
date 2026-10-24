<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAgentMessage extends Model
{
    use HasFactory;

    protected $table = 'ai_agent_messages';

    protected $fillable = [
        'goal_id',
        'from_agent',
        'to_agent',
        'message_type',
        'content',
        'payload',
        'is_read',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(AiAutonomousGoal::class, 'goal_id');
    }
}
