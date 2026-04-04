<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasUuids;

    protected $fillable = [
        'student_id',
        'session_id',
        'type',
        'topic',
        'question',
        'options_json',
        'correct_answer',
        'explanation',
        'student_answer',
        'score',
        'status',
        'answered_at',
    ];

    protected $casts = [
        'options_json' => 'array',
        'score' => 'float',
        'answered_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
