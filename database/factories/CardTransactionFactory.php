<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\CardTransaction;
use App\Models\CreditCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CardTransaction>
 */
class CardTransactionFactory extends Factory
{
    protected $model = CardTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(TransactionType::cases());

        return [
            'credit_card_id' => CreditCard::factory(),
            'transacted_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'description' => $this->generateDescription($type),
            'amount' => $this->generateAmount($type),
            'status' => fake()->randomElement(TransactionStatus::cases()),
            'type' => $type,
            'notes' => fake()->optional(0.3)->sentence(),
            'external_ref' => fake()->optional(0.4)->uuid(),
        ];
    }

    /**
     * Generate realistic merchant descriptions based on type.
     */
    protected function generateDescription(TransactionType $type): string
    {
        return match ($type) {
            TransactionType::Charge => fake()->randomElement([
                'Amazon.com',
                'Walmart',
                'Target',
                'Costco',
                'Whole Foods',
                'Starbucks',
                'Netflix',
                'Spotify',
                'Shell Gas Station',
                'Uber',
                'DoorDash',
                'Home Depot',
            ]),
            TransactionType::Payment => fake()->randomElement([
                'Online Payment',
                'Autopay Payment',
                'Bank Transfer Payment',
                'Check Payment',
            ]),
            TransactionType::Refund => fake()->randomElement([
                'Amazon Refund',
                'Return Credit',
                'Merchant Refund',
                'Dispute Credit',
            ]),
            TransactionType::Fee => fake()->randomElement([
                'Annual Fee',
                'Late Payment Fee',
                'Foreign Transaction Fee',
                'Cash Advance Fee',
                'Balance Transfer Fee',
            ]),
        };
    }

    /**
     * Generate realistic amounts based on type.
     * Note: Model boot will correct sign if needed.
     */
    protected function generateAmount(TransactionType $type): string
    {
        $amount = match ($type) {
            TransactionType::Charge => fake()->randomFloat(2, 5, 500),
            TransactionType::Payment => fake()->randomFloat(2, 50, 2000),
            TransactionType::Refund => fake()->randomFloat(2, 5, 200),
            TransactionType::Fee => fake()->randomFloat(2, 15, 150),
        };

        // Return with correct sign - model will validate anyway
        $sign = $type->expectedSign();

        return number_format(abs($amount) * $sign, 2, '.', '');
    }

    /* ─── States ─── */

    /**
     * Create a charge transaction.
     */
    public function charge(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Charge,
            'description' => $this->generateDescription(TransactionType::Charge),
            'amount' => fake()->randomFloat(2, 5, 500),
        ]);
    }

    /**
     * Create a payment transaction.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Payment,
            'description' => $this->generateDescription(TransactionType::Payment),
            'amount' => fake()->randomFloat(2, 50, 2000),
        ]);
    }

    /**
     * Create a refund transaction.
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Refund,
            'description' => $this->generateDescription(TransactionType::Refund),
            'amount' => fake()->randomFloat(2, 5, 200),
        ]);
    }

    /**
     * Create a fee transaction.
     */
    public function fee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TransactionType::Fee,
            'description' => $this->generateDescription(TransactionType::Fee),
            'amount' => fake()->randomFloat(2, 15, 150),
        ]);
    }

    /**
     * Create a pending transaction.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Pending,
        ]);
    }

    /**
     * Create a posted transaction.
     */
    public function posted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TransactionStatus::Posted,
        ]);
    }
}
