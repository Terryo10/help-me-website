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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable();
            $table->foreignId('donation_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_gateway_id')->constrained()->nullable();

            $table->enum('type', ['donation', 'refund', 'chargeback', 'fee']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled']);

            // Gateway specific
            $table->string('gateway_transaction_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->json('gateway_response')->nullable();

            // Metadata
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['donation_id', 'type']);
            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
