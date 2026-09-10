<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();

            $table->string('code');
            $table->string('name');
            $table->string('type');
            $table->unsignedBigInteger('value');
            $table->string('currency', 3)->nullable();
            $table->unsignedBigInteger('minimum_order_minor')->nullable();
            $table->unsignedBigInteger('maximum_discount_minor')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('active');
            $table->string('scope_type')->default('all');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->json('metadata_json')->nullable();

            $table->timestamps();

            $table->unique(['organization_id', 'code']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
