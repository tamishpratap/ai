<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiAutonomousGoal extends Model
{
    use HasFactory;

    protected $table = 'ai_autonomous_goals';

    protected $fillable = [
        'member_id',
        'project_id',
        'title',
        'user_prompt',
        'interpreted_goal',
        'status',
        'progress_percent',
        'current_milestone',
        'plan',
        'checkpoints',
        'test_results',
        'rollback_metadata',
        'requires_approval',
        'approval_token',
        'final_summary',
    ];

    protected $casts = [
        'plan'              => 'array',
        'checkpoints'       => 'array',
        'test_results'      => 'array',
        'rollback_metadata' => 'array',
        'requires_approval' => 'boolean',
        'progress_percent'  => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AiProject::class, 'project_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(AiAutonomousSubtask::class, 'goal_id')->orderBy('step_number');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiAgentMessage::class, 'goal_id');
    }
}
