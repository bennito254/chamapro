<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('body');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['group_id', 'name']);
        });

        Schema::table('sms_messages', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('group_id')->constrained()->nullOnDelete();
            $table->foreignId('sms_template_id')->nullable()->after('member_id')->constrained()->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->after('body')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sent_by');
            $table->dropConstrainedForeignId('sms_template_id');
            $table->dropConstrainedForeignId('member_id');
        });

        Schema::dropIfExists('sms_templates');
    }
};
