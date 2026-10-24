<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAutonomousSubtask extends Model
{
    use HasFactory;

    protected $table = 'ai_autonomous_subtasks';

    protected $fillable = [
        'goal_id',
        'assigned_agent',
        'step_number',
        'title',
        'instruction',
        'status',
        'payload',
        'output_data',
        'result_summary',
        'validation_checks',
    ];

    protected $casts = [
        'payload'           => 'array',
        'output_data'       => 'array',
        'validation_checks' => 'array',
        'step_number'       => 'integer',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(AiAutonomousGoal::class, 'goal_id');
    }
}
