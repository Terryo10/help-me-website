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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('story'); // Rich text content
            $table->decimal('goal_amount', 12, 2);
            $table->decimal('raised_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // Additional images/videos
            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'completed', 'suspended', 'rejected'])->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->json('beneficiary_info')->nullable(); // Who benefits from the campaign
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('allow_anonymous_donations')->default(true);
            $table->integer('minimum_donation')->default(1);
            $table->integer('view_count')->default(0);
            $table->integer('share_count')->default(0);
            $table->text('admin_notes')->nullable(); // For moderation
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['status', 'is_featured']);
            $table->index(['user_id', 'status']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
