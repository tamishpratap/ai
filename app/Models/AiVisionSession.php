<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVisionSession extends Model
{
    use HasFactory;

    protected $table = 'ai_vision_sessions';

    protected $fillable = [
        'member_id',
        'session_uid',
        'vision_mode',
        'scope',
        'active_window',
        'user_query',
        'confidence',
        'main_issue',
        'observations',
        'root_cause',
        'recommended_fix',
        'alternatives',
        'parsed_error',
        'suggested_actions',
        'ui_review_data',
        'performance_metrics',
        'screenshot_path',
        'ocr_sample',
    ];

    protected $casts = [
        'observations'        => 'array',
        'alternatives'        => 'array',
        'parsed_error'        => 'array',
        'suggested_actions'   => 'array',
        'ui_review_data'      => 'array',
        'performance_metrics' => 'array',
    ];
}
