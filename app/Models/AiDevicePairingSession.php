<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiDevicePairingSession extends Model
{
    protected $table = 'device_pairing_sessions';

    protected $fillable = [
        'member_id',
        'pairing_code_hash',
        'session_identifier',
        'qr_token',
        'status',
        'ip_address',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function isValid(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture() && is_null($this->used_at);
    }

    public function markAsUsed(): void
    {
        $this->update([
            'status'  => 'consumed',
            'used_at' => now(),
        ]);
    }

    public static function generatePairingCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function generateIdentifier(): string
    {
        return (string) Str::uuid();
    }
}
