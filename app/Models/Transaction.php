<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'transaction_reference',
        'amount',
        'currency',
        'status',
        'gateway_response_json',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response_json' => 'array',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'provider_id');
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }
}