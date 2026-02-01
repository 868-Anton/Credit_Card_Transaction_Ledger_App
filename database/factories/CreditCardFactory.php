<?php

namespace Database\Factories;

use App\Models\CreditCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditCard>
 */
class CreditCardFactory extends Factory
{
    protected $model = CreditCard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Chase Sapphire',
                'Amex Gold',
                'Citi Double Cash',
                'Capital One Venture',
                'Discover It',
                'Bank of America Cash Rewards',
            ]).' '.fake()->randomElement(['Visa', 'Mastercard', 'Amex']),
            'currency' => 'USD',
            'credit_limit' => fake()->randomElement([
                '1000.00',
                '2500.00',
                '5000.00',
                '7500.00',
                '10000.00',
                '15000.00',
                '20000.00',
            ]),
            'opened_at' => fake()->dateTimeBetween('-5 years', '-1 month'),
        ];
    }

    /**
     * Configure a card with a high credit limit.
     */
    public function highLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => fake()->randomElement(['25000.00', '50000.00', '75000.00']),
        ]);
    }

    /**
     * Configure a card with a low credit limit.
     */
    public function lowLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => fake()->randomElement(['500.00', '1000.00', '1500.00']),
        ]);
    }
}
