<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contribution_types', function (Blueprint $table) {
            $table->boolean('save_to_bank')->default(true)->after('frequency');
        });

        DB::table('contribution_types')
            ->where('name', 'Welfare Fund')
            ->update(['save_to_bank' => false]);
    }

    public function down(): void
    {
        Schema::table('contribution_types', function (Blueprint $table) {
            $table->dropColumn('save_to_bank');
        });
    }
};
