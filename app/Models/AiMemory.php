<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class AiMemory extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'project_id',
        'category',
        'type',
        'title',
        'content',
        'confidence_level',
        'confidence_score',
        'source',
        'is_confirmed',
        'is_sensitive',
        'access_count',
        'last_accessed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'confidence_score' => 'float',
            'is_confirmed'     => 'boolean',
            'is_sensitive'     => 'boolean',
            'access_count'     => 'integer',
            'last_accessed_at' => 'datetime',
            'metadata'         => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(AiProject::class, 'project_id');
    }

    public function embedding(): HasOne
    {
        return $this->hasOne(AiMemoryEmbedding::class, 'memory_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AiMemoryAuditLog::class, 'memory_id');
    }

    /**
     * Get decrypted content if sensitive.
     */
    public function getDecryptedContentAttribute(): string
    {
        if ($this->is_sensitive && filled($this->content)) {
            try {
                return Crypt::decryptString($this->content);
            } catch (Throwable) {
                return $this->content;
            }
        }

        return (string) $this->content;
    }

    /**
     * Set content, encrypting if sensitive.
     */
    public function setSafeContent(string $value, bool $sensitive = false): self
    {
        $this->is_sensitive = $sensitive;
        if ($sensitive) {
            $this->content = Crypt::encryptString($value);
        } else {
            $this->content = $value;
        }

        return $this;
    }

    public function touchAccess(): void
    {
        $this->timestamps = false;
        $this->increment('access_count');
        $this->update(['last_accessed_at' => now()]);
        $this->timestamps = true;
    }

    public function scopeForMember($query, ?int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    public function scopeHighConfidence($query)
    {
        return $query->where(function ($q) {
            $q->where('is_confirmed', true)
              ->orWhere('confidence_score', '>=', 0.75)
              ->orWhereIn('confidence_level', ['confirmed', 'high']);
        });
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}
