<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVisionSetting extends Model
{
    use HasFactory;

    protected $table = 'ai_vision_settings';

    protected $fillable = [
        'member_id',
        'screen_watch_enabled',
        'screen_watch_paused',
        'watch_interval_sec',
        'default_scope',
        'default_mode',
        'blocked_apps',
        'allowed_apps',
        'save_history',
        'retention_days',
        'privacy_regions',
    ];

    protected $casts = [
        'screen_watch_enabled' => 'boolean',
        'screen_watch_paused'  => 'boolean',
        'watch_interval_sec'   => 'integer',
        'blocked_apps'         => 'array',
        'allowed_apps'         => 'array',
        'save_history'         => 'boolean',
        'retention_days'       => 'integer',
        'privacy_regions'      => 'array',
    ];
}
