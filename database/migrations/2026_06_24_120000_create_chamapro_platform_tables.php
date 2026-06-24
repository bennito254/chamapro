<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });

        Schema::create('super_admin_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('billing_cycle');
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->unsignedInteger('max_members');
            $table->unsignedInteger('max_users');
            $table->unsignedInteger('trial_days')->default(14);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('sms_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver');
            $table->text('credentials')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('sms_providers');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('super_admin_password_reset_tokens');
        Schema::dropIfExists('super_admins');
    }
};
