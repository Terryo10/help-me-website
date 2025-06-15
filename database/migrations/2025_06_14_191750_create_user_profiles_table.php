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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Non-profit specific fields
            $table->string('organization_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('mission_statement')->nullable();
            $table->year('founded_year')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->json('contact_persons')->nullable(); // Array of contact people
            $table->json('bank_details')->nullable(); // Encrypted bank info
            $table->json('verification_documents')->nullable(); // Document paths
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['user_id', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
