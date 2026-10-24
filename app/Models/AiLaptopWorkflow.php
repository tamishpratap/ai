<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AiLaptopWorkflow extends Model
{
    use HasFactory;

    protected $table = 'ai_laptop_workflows';

    protected $fillable = [
        'member_id',
        'name',
        'slug',
        'description',
        'trigger_phrases',
        'steps',
        'is_system',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'trigger_phrases' => 'array',
        'steps'           => 'array',
        'metadata'        => 'array',
        'is_system'       => 'boolean',
        'is_active'       => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($workflow) {
            if (empty($workflow->slug) && !empty($workflow->name)) {
                $workflow->slug = Str::slug($workflow->name) . '-' . Str::random(5);
            }
        });
    }

    public function member()
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
