<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('session_id', 64);
            $table->enum('type', ['multiple_choice', 'fill_in_blank', 'translation', 'error_correction']);
            $table->string('topic', 100);
            $table->text('question');
            $table->json('options_json')->nullable();
            $table->text('correct_answer');
            $table->text('explanation');
            $table->text('student_answer')->nullable();
            $table->float('score')->nullable();
            $table->enum('status', ['pending', 'answered'])->default('pending');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
