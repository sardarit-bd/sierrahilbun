<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'title',
        'subtitle',
        'icon_url',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'image_url'  => 'string',
        'sort_order' => 'integer',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_feature')
                    ->withPivot('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'feature_product',
            'feature_id',
            'product_sku',
            'id',
            'sku'
        );
    }
}