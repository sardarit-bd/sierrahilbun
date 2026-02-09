<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'subscription_id',
        'scheduled_date',
        'status',
        'manifest_json',
        'tracking_number',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'manifest_json' => 'array',
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}