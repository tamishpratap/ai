<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTaskEvaluation extends Model
{
    use HasFactory;

    protected $table = 'ai_task_evaluations';

    protected $fillable = [
        'member_id',
        'task_description',
        'agent_used',
        'feedback',
        'user_comment',
        'execution_time_ms',
        'metadata',
    ];

    protected $casts = [
        'metadata'          => 'array',
        'execution_time_ms' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }
}
