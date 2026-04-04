<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_library', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id')->unique();
            $table->string('title');
            $table->string('channel');
            $table->string('cefr_level');
            $table->json('topics');
            $table->json('clips')->nullable();
            $table->enum('content_type', ['lesson', 'movie_clip', 'news', 'comedy', 'ted_talk', 'pronunciation']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_library');
    }
};
