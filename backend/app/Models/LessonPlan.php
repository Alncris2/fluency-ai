<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPlan extends Model
{
    protected $fillable = [
        'student_id',
        'current_unit',
        'current_lesson',
        'curriculum',
        'completed_topics',
        'weak_areas',
        'sessions_per_week',
        'apa_cycles_completed',
    ];

    protected $casts = [
        'curriculum' => 'array',
        'completed_topics' => 'array',
        'weak_areas' => 'array',
        'current_unit' => 'integer',
        'current_lesson' => 'integer',
        'sessions_per_week' => 'integer',
        'apa_cycles_completed' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
