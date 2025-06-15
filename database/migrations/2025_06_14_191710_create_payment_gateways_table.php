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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // EcoCash, OneMoney, PayPal, etc.
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('provider_class'); // Class handling the gateway
            $table->json('configuration'); // Gateway-specific config
            $table->decimal('fee_percentage', 5, 2)->default(0); // e.g., 2.50%
            $table->decimal('fee_fixed', 10, 2)->default(0); // Fixed fee amount
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_refunds')->default(false);
            $table->string('logo')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
