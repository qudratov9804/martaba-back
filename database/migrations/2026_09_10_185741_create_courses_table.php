<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('thumbnail_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('intro_video_path')->nullable();

            $table->string('level')->default('all_levels');
            $table->string('language', 5)->default('en');

            $table->string('pricing_type')->default('free');
            $table->unsignedBigInteger('price_minor')->default(0);
            $table->unsignedBigInteger('discount_price_minor')->nullable();
            $table->string('currency', 3)->default('USD');

            $table->string('status')->default('draft');
            $table->string('visibility')->default('private');

            $table->boolean('featured')->default(false);
            $table->boolean('certificate_enabled')->default(false);
            $table->boolean('reviews_enabled')->default(true);

            $table->unsignedInteger('estimated_duration_minutes')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();

            $table->json('completion_rules_json')->nullable();
            $table->json('settings_json')->nullable();
            $table->json('metadata_json')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status']);
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
