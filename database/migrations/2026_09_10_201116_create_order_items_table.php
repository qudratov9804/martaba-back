<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();

            $table->string('title_snapshot');
            $table->unsignedBigInteger('price_minor');
            $table->unsignedBigInteger('discount_minor')->default(0);
            $table->unsignedBigInteger('total_minor');
            $table->string('currency', 3);

            $table->timestamp('created_at')->nullable();

            $table->unique(['order_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
