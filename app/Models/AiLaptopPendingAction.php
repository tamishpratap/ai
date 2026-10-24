<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLaptopPendingAction extends Model
{
    public $timestamps = false;

    protected $table = 'ai_laptop_pending_actions';

    protected $fillable = [
        'member_id',
        'token',
        'action_type',
        'risk_level',
        'title',
        'description',
        'payload',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'payload'    => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
