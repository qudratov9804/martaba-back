<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->cascadeOnDelete();

            $table->string('type');
            $table->text('question_text');
            $table->text('explanation')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->string('difficulty')->default('medium');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
