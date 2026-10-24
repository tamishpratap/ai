<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDeviceAuditLog extends Model
{
    protected $table = 'device_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'member_id',
        'event_type',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(AiDevice::class, 'device_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public static function log(
        string $eventType,
        int $memberId,
        ?int $deviceId = null,
        ?array $metadata = null,
        ?string $ipAddress = null
    ): self {
        return self::create([
            'event_type' => $eventType,
            'member_id'  => $memberId,
            'device_id'  => $deviceId,
            'metadata'   => $metadata,
            'ip_address' => $ipAddress ?: request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
