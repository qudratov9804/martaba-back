<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();

            $table->foreignId('certificate_template_id')->nullable()->constrained()->nullOnDelete();

            $table->string('certificate_number')->unique();
            $table->string('verification_code')->unique();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('student_name_snapshot');
            $table->string('course_name_snapshot');
            $table->string('teacher_name_snapshot')->nullable();
            $table->string('organization_name_snapshot');

            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();

            $table->string('pdf_path')->nullable();
            $table->string('qr_code_path')->nullable();

            $table->string('status')->default('issued');
            $table->json('metadata_json')->nullable();

            $table->timestamps();

            $table->index(['enrollment_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
