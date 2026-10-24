<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AiProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'name',
        'slug',
        'description',
        'tech_stack',
        'architecture',
        'folders_structure',
        'api_endpoints',
        'known_bugs',
        'fixed_bugs',
        'current_progress',
        'pending_work',
        'next_steps',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function (AiProject $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function memories(): HasMany
    {
        return $this->hasMany(AiMemory::class, 'project_id');
    }

    public function scopeForMember($query, ?int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
