<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->text('description');
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });

        Schema::create('support_ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('author');
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });

        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient');
            $table->text('body');
            $table->string('provider')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });

        Schema::create('mpesa_callback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id')->nullable();
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->index('transaction_id');
        });

        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id')->unique();
            $table->string('phone_number');
            $table->decimal('amount', 12, 2);
            $table->string('type');
            $table->string('status');
            $table->string('reference')->nullable();
            $table->nullableMorphs('payable');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });

        Schema::create('dividend_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('total_profit', 14, 2);
            $table->json('formula')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'year']);
        });

        Schema::create('dividend_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('member_contributions', 14, 2);
            $table->decimal('ownership_percentage', 8, 4);
            $table->decimal('dividend_amount', 14, 2);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('dividend_allocations');
        Schema::dropIfExists('dividend_runs');
        Schema::dropIfExists('mpesa_transactions');
        Schema::dropIfExists('mpesa_callback_logs');
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('support_ticket_notes');
        Schema::dropIfExists('support_tickets');
    }
};
