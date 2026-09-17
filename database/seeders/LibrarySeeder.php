<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Seeder;

class LibrarySeeder extends Seeder
{
    /**
     * Seed the library data.
     */
    public function run(): void
    {
        $authors = Author::factory(20)->create();
        $categories = Category::factory(10)->create();
        $members = Member::factory(15)->create();
        $books = Book::factory(100)->create();

        $books->each(function (Book $book) use ($authors, $categories): void {
            $book->authors()->attach(
                $authors->random(fake()->numberBetween(1, 3))->modelKeys()
            );

            $book->categories()->attach(
                $categories->random(fake()->numberBetween(1, 3))->modelKeys()
            );
        });

        $loanBooks = $books->shuffle();
        $activeBooks = $loanBooks->take(20);
        $returnedBooks = $loanBooks->skip(20)->take(40);

        $activeBooks->each(function (Book $book) use ($members): void {
            $loanedAt = now()->subDays(fake()->numberBetween(1, 7));

            Loan::factory()->active()->create([
                'member_id' => $members->random()->id,
                'book_id' => $book->id,
                'loaned_at' => $loanedAt,
                'due_at' => now()->addDays(fake()->numberBetween(1, 14)),
            ]);
        });

        $returnedBooks->each(function (Book $book) use ($members): void {
            Loan::factory()->returned()->create([
                'member_id' => $members->random()->id,
                'book_id' => $book->id,
                'loaned_at' => now()->subDays(fake()->numberBetween(30, 365)),
            ]);
        });
    }
}
