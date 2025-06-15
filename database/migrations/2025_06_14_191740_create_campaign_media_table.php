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
        Schema::create('campaign_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video', 'document']);
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('url')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // in bytes
            $table->json('metadata')->nullable(); // dimensions, duration, etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['campaign_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_media');
    }
};
