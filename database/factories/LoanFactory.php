<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loanedAt = fake()->dateTimeBetween('-1 year', '-1 month');

        return [
            'member_id' => Member::factory(),
            'book_id' => Book::factory(),
            'loaned_at' => $loanedAt,
            'due_at' => Carbon::instance($loanedAt)->addDays(14),
            'returned_at' => null,
            'active' => true,
        ];
    }

    /**
     * Mark the loan as active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'active' => true,
            'returned_at' => null,
            'due_at' => Carbon::parse($attributes['loaned_at'])->addDays(14),
        ]);
    }

    /**
     * Mark the loan as returned.
     */
    public function returned(): static
    {
        return $this->state(function (array $attributes): array {
            $loanedAt = Carbon::parse($attributes['loaned_at']);
            $returnedAt = $loanedAt->copy()->addDays(fake()->numberBetween(1, 14));

            return [
                'active' => false,
                'due_at' => $loanedAt->copy()->addDays(14),
                'returned_at' => $returnedAt,
            ];
        });
    }
}
