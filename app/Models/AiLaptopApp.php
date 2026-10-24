<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLaptopApp extends Model
{
    protected $table = 'ai_laptop_apps';

    protected $fillable = [
        'member_id',
        'app_key',
        'name',
        'executable_path',
        'process_name',
        'is_installed',
        'is_allowlisted',
        'category',
    ];

    protected $casts = [
        'is_installed'   => 'boolean',
        'is_allowlisted' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(MemberDetail::class, 'member_id');
    }
}
