<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StakingDetail extends Model
{
    use HasFactory;

    protected $casts = [
        'is_upgrade' => 'boolean',
    ];
}
