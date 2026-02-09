<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionQuizAnswer extends Model
{
    protected $fillable = [
        'session_id',
        'question_key',
        'answer_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function session(): BelongsTo
    {
        return $this->belongsTo(OnboardingSession::class, 'session_id');
    }
}