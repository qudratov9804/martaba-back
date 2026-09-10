<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();

            $table->string('provider');
            $table->string('status')->default('pending');

            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3);

            $table->string('provider_payment_id')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->string('idempotency_key')->unique();

            $table->string('failure_code')->nullable();
            $table->string('failure_message')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->json('metadata_json')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['provider', 'provider_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
