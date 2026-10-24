<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    use HasUuids;

    protected $fillable = ['conversation_id', 'member_id', 'role', 'content', 'input_type', 'output_type', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
