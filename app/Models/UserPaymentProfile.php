<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPaymentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'external_customer_id',
        'external_payment_method_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'payment_profile_id');
    }
}