<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}