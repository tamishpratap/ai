<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiDeviceToken extends Model
{
    protected $table = 'device_tokens';

    protected $fillable = [
        'device_id',
        'token_hash',
        'token_type',
        'name',
        'is_revoked',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_revoked'   => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(AiDevice::class, 'device_id');
    }

    public function isValid(): bool
    {
        if ($this->is_revoked) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public static function generatePlainToken(): string
    {
        return 'jarvis_dev_' . Str::random(60);
    }
}
