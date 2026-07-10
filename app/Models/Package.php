<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'is_active' => 'boolean',
    ];
}
