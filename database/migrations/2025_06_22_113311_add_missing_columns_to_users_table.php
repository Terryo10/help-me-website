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
        Schema::table('users', function (Blueprint $table) {
            // Add missing columns that are referenced in User model and Filament resources
            $table->string('phone')->nullable()->after('email');
            $table->enum('user_type', ['individual', 'non_profit'])->default('individual')->after('password');
            $table->enum('status', ['active', 'inactive', 'suspended', 'banned'])->default('active')->after('user_type');
            $table->string('avatar')->nullable()->after('status');
            $table->date('date_of_birth')->nullable()->after('avatar');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('date_of_birth');
            $table->string('country')->nullable()->after('gender');
            $table->string('city')->nullable()->after('country');
            $table->text('bio')->nullable()->after('city');
            $table->string('website')->nullable()->after('bio');
            $table->json('social_links')->nullable()->after('website');
            $table->boolean('email_notifications')->default(true)->after('social_links');
            $table->boolean('sms_notifications')->default(true)->after('email_notifications');
            $table->timestamp('phone_verified_at')->nullable()->after('sms_notifications');
            $table->timestamp('last_login_at')->nullable()->after('phone_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'user_type',
                'status',
                'avatar',
                'date_of_birth',
                'gender',
                'country',
                'city',
                'bio',
                'website',
                'social_links',
                'email_notifications',
                'sms_notifications',
                'phone_verified_at',
                'last_login_at',
            ]);
        });
    }
};
