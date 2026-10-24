<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AiLaptopDevice extends Model
{
    protected $table = 'ai_laptop_devices';

    protected $fillable = [
        'member_id',
        'device_uuid',
        'device_name',
        'platform',
        'ip_address',
        'auth_token',
        'status',
        'is_approved',
        'approved_at',
        'last_seen_at',
        'metadata',
    ];

    protected $casts = [
        'is_approved'  => 'boolean',
        'approved_at'  => 'datetime',
        'last_seen_at' => 'datetime',
        'metadata'     => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public static function generateToken(): string
    {
        return 'jarvis_dev_' . Str::random(40);
    }
}
