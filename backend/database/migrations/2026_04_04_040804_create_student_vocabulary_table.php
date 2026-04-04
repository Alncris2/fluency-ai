<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('word');
            $table->text('definition');
            $table->text('example_sentence');
            $table->string('context')->nullable();
            $table->tinyInteger('difficulty')->default(1);
            $table->integer('times_seen')->default(1);
            $table->integer('times_correct')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('mastered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_vocabulary');
    }
};
