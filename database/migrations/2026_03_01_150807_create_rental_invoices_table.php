<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->date('payment_made_on')->nullable();
            $table->date('due_date');
            $table->string('tenant_name');
            $table->text('tenant_address')->nullable();
            $table->string('tenant_phone')->nullable();
            $table->string('tenant_email')->nullable();
            $table->string('description');
            $table->decimal('rent_amount', 10, 2);
            $table->decimal('additional_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('landlord_name');
            $table->string('landlord_address');
            $table->string('landlord_phone');
            $table->string('landlord_email');
            $table->text('notes')->nullable();
            $table->string('status')->default('paid');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_invoices');
    }
};
