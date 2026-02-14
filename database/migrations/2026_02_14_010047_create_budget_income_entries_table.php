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
        Schema::create('budget_income_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_month_id')
                ->constrained('budget_months')
                ->cascadeOnDelete();
            $table->string('label');
            $table->string('type');
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_income_entries');
    }
};
