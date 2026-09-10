<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();

            $table->string('type');
            $table->string('status');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('provider_transaction_id')->nullable();
            $table->json('request_payload_json')->nullable();
            $table->json('response_payload_json')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['payment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
