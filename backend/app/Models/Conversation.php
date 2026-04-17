<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'student_id',
        'session_id',
        'messages',
        'session_type',
        'tokens_used',
        'last_activity_at',
    ];

    protected $casts = [
        'messages' => 'array',
        'tokens_used' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
