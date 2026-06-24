<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('membership_number');
            $table->string('full_name');
            $table->string('id_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_joined');
            $table->text('address')->nullable();
            $table->string('occupation')->nullable();
            $table->string('next_of_kin')->nullable();
            $table->string('next_of_kin_phone')->nullable();
            $table->string('photo')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['group_id', 'membership_number']);
            $table->index(['group_id', 'status']);
            $table->index(['group_id', 'created_at']);
        });

        Schema::create('contribution_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->string('amount_type');
            $table->string('frequency');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });

        Schema::create('contribution_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });

        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contribution_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contribution_channel_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('transaction_reference')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'date']);
            $table->index(['group_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
        Schema::dropIfExists('contribution_channels');
        Schema::dropIfExists('contribution_types');
        Schema::dropIfExists('members');
    }
};
