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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reportable'); // Can report campaigns, users, etc.
            $table->enum('type', ['inappropriate_content', 'fraud', 'spam', 'copyright', 'other']);
            $table->text('reason');
            $table->json('evidence')->nullable(); // Screenshots, links, etc.
            $table->enum('status', ['pending', 'investigating', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
