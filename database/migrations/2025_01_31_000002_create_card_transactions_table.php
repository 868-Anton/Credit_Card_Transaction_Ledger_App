<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('card_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('credit_card_id')->constrained()->cascadeOnDelete();
      $table->date('transacted_at');                   // when the transaction occurred
      $table->string('description');                   // merchant / label
      $table->decimal('amount', 12, 2);                // positive = charge, negative = payment
      $table->string('status')->default('pending');    // pending | posted  (enum value)
      $table->string('type')->default('charge');       // charge | payment | refund | fee
      $table->text('notes')->nullable();               // free-text notes / category label
      $table->string('external_ref')->nullable();      // bank reference ID (future use)
      $table->timestamps();

      // indexes for the queries your dashboard runs constantly
      $table->index('status');
      $table->index('transacted_at');
      $table->index(['credit_card_id', 'status']);     // composite: filter by card + status
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('card_transactions');
  }
};
