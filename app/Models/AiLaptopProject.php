<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiLaptopProject extends Model
{
    protected $table = 'ai_laptop_projects';

    protected $fillable = [
        'member_id',
        'name',
        'slug',
        'project_path',
        'project_type',
        'preferred_ide',
        'start_command',
        'stop_command',
        'related_services',
        'browser_url',
        'last_opened_at',
    ];

    protected $casts = [
        'related_services' => 'array',
        'last_opened_at'   => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($proj) {
            if (empty($proj->slug)) {
                $proj->slug = Str::slug($proj->name);
            }
        });
    }
}
