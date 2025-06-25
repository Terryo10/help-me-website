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
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('id');
            $table->unsignedBigInteger('user_id')->nullable()->after('donation_id');
            $table->decimal('total', 10, 2)->nullable()->after('amount');
            $table->string('isPaid')->default('false')->after('status');
            $table->string('poll_url')->nullable()->after('gateway_response');

            // Add foreign key for user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Add index for order_id
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn(['order_id', 'user_id', 'total', 'isPaid', 'poll_url']);
        });
    }
};
