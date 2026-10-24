<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDevicePermission extends Model
{
    protected $table = 'device_permissions';

    protected $fillable = [
        'device_id',
        'permission_key',
        'status',
        'granted_at',
        'revoked_at',
        'last_checked_at',
    ];

    protected $casts = [
        'granted_at'      => 'datetime',
        'revoked_at'      => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(AiDevice::class, 'device_id');
    }

    public function isGranted(): bool
    {
        return $this->status === 'granted';
    }
}
