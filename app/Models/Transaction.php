<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_id',
        'gateway',
        'amount',
        'currency',
        'status',
        'payment_method_id',
        'error_message',
        'raw_response',
        'payable_id',
        'payable_type',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'amount'       => 'decimal:2',
    ];

    protected $hidden = [
        'raw_response',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}