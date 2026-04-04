<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('current_unit')->default(1);
            $table->integer('current_lesson')->default(1);
            $table->json('curriculum')->nullable();
            $table->json('completed_topics')->default('[]');
            $table->json('weak_areas')->default('[]');
            $table->integer('sessions_per_week')->default(3);
            $table->integer('apa_cycles_completed')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
