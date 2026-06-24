<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_amount', 14, 2);
            $table->decimal('max_multiplier', 8, 2)->default(3);
            $table->string('interest_type');
            $table->decimal('interest_value', 12, 2);
            $table->unsignedInteger('repayment_period');
            $table->unsignedInteger('grace_period')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });

        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_product_id')->constrained()->cascadeOnDelete();
            $table->decimal('requested_amount', 14, 2);
            $table->text('purpose')->nullable();
            $table->string('status');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'status']);
            $table->index(['group_id', 'member_id']);
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('interest_type');
            $table->decimal('interest_value', 12, 2);
            $table->unsignedInteger('repayment_period');
            $table->unsignedInteger('grace_period')->default(0);
            $table->decimal('principal_amount', 14, 2);
            $table->decimal('interest_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->decimal('outstanding_balance', 14, 2);
            $table->date('disbursement_date');
            $table->date('due_date')->nullable();
            $table->string('status');
            $table->foreignId('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'status']);
            $table->index(['group_id', 'member_id']);
        });

        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('guaranteed_amount', 14, 2);
            $table->timestamps();

            $table->index(['group_id', 'member_id']);
        });

        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->decimal('principal_paid', 14, 2)->default(0);
            $table->decimal('interest_paid', 14, 2)->default(0);
            $table->decimal('balance_after', 14, 2);
            $table->date('date');
            $table->string('method')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_id', 'date']);
            $table->index(['group_id', 'loan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('loan_guarantors');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_applications');
        Schema::dropIfExists('loan_products');
    }
};
