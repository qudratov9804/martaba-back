<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->unsignedInteger('completed_lessons')->default(0);
            $table->unsignedInteger('total_lessons')->default(0);
            $table->unsignedInteger('required_lessons_completed')->default(0);
            $table->unsignedInteger('required_lessons_total')->default(0);
            $table->foreignId('last_lesson_id')->nullable()->constrained('course_lessons')->nullOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_progress');
    }
};
