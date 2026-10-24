<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProjectBrainEntry extends Model
{
    use HasFactory;

    protected $table = 'ai_project_brain_entries';

    protected $fillable = [
        'member_id',
        'project_id',
        'entry_type',
        'title',
        'content',
        'file_path',
        'status',
        'priority',
        'tags',
        'relationships',
        'metadata',
    ];

    protected $casts = [
        'tags'          => 'array',
        'relationships' => 'array',
        'metadata'      => 'array',
        'priority'      => 'integer',
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
