<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = ['member_id', 'conversation_id', 'message_id', 'model', 'request_type', 'input_type', 'output_type', 'input_tokens', 'output_tokens', 'total_tokens', 'duration_ms', 'status', 'error_type'];
}
