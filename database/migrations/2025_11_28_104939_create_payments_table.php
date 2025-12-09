<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('square_payment_id')->unique()->nullable()->default(null);
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('status');
            $table->string('customer_id')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('order_id')->nullable();
            $table->text('note')->nullable();
            $table->json('payment_data')->nullable(); // Store complete Square response
            $table->json('request_data')->nullable(); // Store request data
            $table->string('idempotency_key')->nullable();
            $table->string('location_id')->nullable();
            $table->string('source_type')->nullable();
            $table->json('error_message')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['square_payment_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
