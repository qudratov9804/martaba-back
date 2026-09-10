<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('extension', 20);
            $table->unsignedBigInteger('size');
            $table->string('visibility')->default('private');
            $table->string('checksum')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'mime_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
