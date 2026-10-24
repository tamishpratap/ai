<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiReusableSkill extends Model
{
    use HasFactory;

    protected $table = 'ai_reusable_skills';

    protected $fillable = [
        'member_id',
        'skill_name',
        'slug',
        'trigger_phrases',
        'action_type',
        'action_payload',
        'is_active',
        'success_count',
    ];

    protected $casts = [
        'trigger_phrases' => 'array',
        'action_payload'  => 'array',
        'is_active'       => 'boolean',
        'success_count'   => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }
}
