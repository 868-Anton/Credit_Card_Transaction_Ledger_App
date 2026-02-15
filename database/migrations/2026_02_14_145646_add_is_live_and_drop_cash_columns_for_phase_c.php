<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_live to budget_income_entries
        Schema::table('budget_income_entries', function (Blueprint $table) {
            $table->boolean('is_live')->default(true)->after('notes');
        });

        // 2. Drop cash columns from budget_months
        Schema::table('budget_months', function (Blueprint $table) {
            $table->dropColumn(['cash_in_bank', 'cash_in_hand']);
        });
    }

    public function down(): void
    {
        Schema::table('budget_income_entries', function (Blueprint $table) {
            $table->dropColumn('is_live');
        });

        Schema::table('budget_months', function (Blueprint $table) {
            $table->decimal('cash_in_bank', 12, 2)->nullable();
            $table->decimal('cash_in_hand', 12, 2)->nullable();
        });
    }
};
