<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiDeviceCommand extends Model
{
    protected $table = 'device_commands';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_SENT      = 'sent';
    public const STATUS_RECEIVED  = 'received';
    public const STATUS_EXECUTING = 'executing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DENIED    = 'denied';

    protected $fillable = [
        'command_uuid',
        'device_id',
        'member_id',
        'command_type',
        'payload',
        'status',
        'result',
        'error_code',
        'error_message',
        'expires_at',
        'sent_at',
        'received_at',
        'executed_at',
    ];

    protected $casts = [
        'payload'     => 'array',
        'result'      => 'array',
        'expires_at'  => 'datetime',
        'sent_at'     => 'datetime',
        'received_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(AiDevice::class, 'device_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_EXPIRED,
            self::STATUS_CANCELLED,
            self::STATUS_DENIED,
        ], true);
    }

    public static function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}
