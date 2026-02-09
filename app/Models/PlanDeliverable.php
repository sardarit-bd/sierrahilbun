<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanDeliverable extends Model
{
    protected $fillable = [
        'plan_id',
        'product_sku',
        'quantity',
        'shipment_month',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'shipment_month' => 'integer',
    ];

    public $timestamps = false;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_sku', 'sku');
    }
}