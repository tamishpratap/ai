<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinaryIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'memberid',
        'closing_date',
        'left_forwarded',
        'left_new',
        'total_left',
        'right_forwarded',
        'right_new',
        'total_right',
        'matching',
        'total_amount',
        'flushout',
        'amount',
        'forward_left',
        'forward_right',
        'installments',
        'status',
    ];

    protected $casts = [
        'closing_date' => 'date',
    ];
}
