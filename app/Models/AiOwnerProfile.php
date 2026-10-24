<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiOwnerProfile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'languages' => 'array',
        'communication_preferences' => 'array',
        'technical_stack' => 'array',
        'active_projects' => 'array',
        'personal_preferences' => 'array',
        'is_active' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }
}
