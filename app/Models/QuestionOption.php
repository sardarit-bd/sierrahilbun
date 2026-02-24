<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_id', 'slug', 'label', 'image_url',
        'tag', 'description', 'is_recommended', 'is_more',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_recommended' => 'boolean',
        'is_more'        => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}