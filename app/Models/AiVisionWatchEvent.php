<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVisionWatchEvent extends Model
{
    use HasFactory;

    protected $table = 'ai_vision_watch_events';

    protected $fillable = [
        'member_id',
        'event_category',
        'notification_level',
        'active_window',
        'message',
        'details',
        'is_acknowledged',
    ];

    protected $casts = [
        'details'         => 'array',
        'is_acknowledged' => 'boolean',
    ];
}
