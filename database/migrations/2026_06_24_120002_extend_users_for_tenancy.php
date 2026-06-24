<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('avatar')->nullable()->after('password');
            $table->string('status')->default('active')->after('avatar');
            $table->timestamp('last_login')->nullable()->after('status');
            $table->dropUnique(['email']);
            $table->unique(['group_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'email']);
            $table->unique('email');
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id', 'avatar', 'status', 'last_login']);
        });
    }
};
