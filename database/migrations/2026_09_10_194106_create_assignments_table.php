<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('course_lessons')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();

            $table->unsignedInteger('max_score')->default(100);
            $table->unsignedInteger('passing_score')->default(60);
            $table->timestamp('deadline')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->json('allowed_file_types_json')->nullable();
            $table->unsignedInteger('max_file_size')->nullable();
            $table->boolean('is_required')->default(true);

            $table->timestamps();

            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
