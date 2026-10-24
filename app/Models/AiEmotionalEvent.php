<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiEmotionalEvent extends Model
{
    use HasFactory;

    protected $table = 'ai_interaction_events';

    protected $fillable = [
        'member_id',
        'project_id',
        'event_type',
        'event_description',
        'context',
        'resulting_state',
        'importance',
        'resolved',
        'metadata',
    ];

    protected $casts = [
        'importance' => 'float',
        'resolved' => 'boolean',
        'metadata' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AiProject::class, 'project_id');
    }
}
