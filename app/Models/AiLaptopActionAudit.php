<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLaptopActionAudit extends Model
{
    public $timestamps = false;

    protected $table = 'ai_laptop_action_audits';

    protected $fillable = [
        'member_id',
        'action_type',
        'target',
        'risk_level',
        'status',
        'details',
        'output',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }
}
