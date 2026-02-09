<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentProvider extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = false;

    public function paymentProfiles(): HasMany
    {
        return $this->hasMany(UserPaymentProfile::class, 'provider_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'provider_id');
    }
}