<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $fillable = [
        'plan_id',
        'title',
        'subtitle',
        'icon_url',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public $timestamps = false;

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}