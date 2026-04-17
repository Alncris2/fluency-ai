<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'level',
        'preferences',
        'subscription_plan',
        'streak_current',
        'streak_best',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function learningPlan(): HasOne
    {
        return $this->hasOne(LessonPlan::class);
    }
}
