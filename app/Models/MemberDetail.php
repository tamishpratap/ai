<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberDetail extends Model
{
    use HasFactory;

    protected $casts = [
        'capped_income_earned' => 'decimal:8',
        'income_cap_limit' => 'decimal:8',
    ];
}
