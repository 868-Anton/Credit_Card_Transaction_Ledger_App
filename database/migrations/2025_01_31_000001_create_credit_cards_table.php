<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('credit_cards', function (Blueprint $table) {
      $table->id();
      $table->string('name');                          // e.g. "RBC Visa", "Scotia MC"
      $table->string('currency', 3)->default('USD');   // ISO 4217, keep for future
      $table->decimal('credit_limit', 12, 2);          // max credit available
      $table->date('opened_at')->nullable();           // when the card was opened
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('credit_cards');
  }
};
