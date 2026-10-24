<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    use HasUuids;

    protected $fillable = ['member_id', 'title', 'provider', 'model', 'mode', 'status', 'summary', 'started_at', 'last_message_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'last_message_at' => 'datetime'];
    }

    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }
}
