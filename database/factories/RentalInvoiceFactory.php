<?php

namespace Database\Factories;

use App\Enums\RentalInvoiceStatus;
use App\Models\RentalInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalInvoice>
 */
class RentalInvoiceFactory extends Factory
{
    protected $model = RentalInvoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rent = fake()->randomFloat(2, 1000, 5000);
        $additional = fake()->boolean(30) ? fake()->randomFloat(2, 50, 500) : 0;

        return [
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'payment_made_on' => fake()->optional(0.8)->dateTimeBetween('-3 months', 'now'),
            'due_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'tenant_name' => fake()->name().' & '.fake()->name(),
            'tenant_address' => fake()->optional()->address(),
            'tenant_phone' => fake()->optional()->phoneNumber(),
            'tenant_email' => fake()->optional()->safeEmail(),
            'description' => 'Monthly Rent for '.fake()->monthName().' '.fake()->year(),
            'rent_amount' => $rent,
            'additional_charges' => $additional,
            'total_amount' => bcadd((string) $rent, (string) $additional, 2),
            'landlord_name' => config('rental.landlord.name'),
            'landlord_address' => config('rental.landlord.address'),
            'landlord_phone' => config('rental.landlord.phone'),
            'landlord_email' => config('rental.landlord.email'),
            'notes' => fake()->optional()->sentence(),
            'status' => RentalInvoiceStatus::Paid,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => RentalInvoiceStatus::Paid,
            'payment_made_on' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (): array => [
            'status' => RentalInvoiceStatus::Unpaid,
            'payment_made_on' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'status' => RentalInvoiceStatus::Overdue,
            'payment_made_on' => null,
            'due_date' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }
}
