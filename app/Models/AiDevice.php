<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AiDevice extends Model
{
    protected $table = 'ai_devices';

    protected $fillable = [
        'member_id',
        'device_uuid',
        'device_name',
        'device_type',
        'platform',
        'os_version',
        'app_version',
        'model_name',
        'manufacturer',
        'status',
        'battery_level',
        'is_charging',
        'network_type',
        'wifi_ssid',
        'ip_address',
        'storage_free_mb',
        'storage_total_mb',
        'last_seen_at',
        'paired_at',
        'revoked_at',
        'metadata',
    ];

    protected $casts = [
        'is_charging'      => 'boolean',
        'battery_level'    => 'integer',
        'storage_free_mb'  => 'integer',
        'storage_total_mb' => 'integer',
        'last_seen_at'     => 'datetime',
        'paired_at'        => 'datetime',
        'revoked_at'       => 'datetime',
        'metadata'         => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(AiDeviceToken::class, 'device_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(AiDevicePermission::class, 'device_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(AiDeviceCommand::class, 'device_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AiDeviceAuditLog::class, 'device_id');
    }

    public function isOnline(int $thresholdSeconds = 120): bool
    {
        if ($this->status === 'revoked') {
            return false;
        }

        return $this->last_seen_at && $this->last_seen_at->diffInSeconds(now()) <= $thresholdSeconds;
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked' || !is_null($this->revoked_at);
    }

    public function revoke(): void
    {
        $this->update([
            'status'     => 'revoked',
            'revoked_at' => now(),
        ]);

        // Invalidate all tokens for this device
        $this->tokens()->update(['is_revoked' => true]);
    }

    public static function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}
