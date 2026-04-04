<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_video_history', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('youtube_id');
            $table->integer('start_seconds')->nullable();
            $table->boolean('was_helpful')->nullable();
            $table->timestamps();

            $table->index('youtube_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_video_history');
    }
};
