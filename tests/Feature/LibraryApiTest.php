<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authors support the complete API resource lifecycle.
     */
    public function test_authors_support_crud_and_resources(): void
    {
        $existingAuthor = Author::factory()->create();

        $this->getJson('/api/authors')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $existingAuthor->id,
                'name' => $existingAuthor->name,
            ]);

        $response = $this->postJson('/api/authors', [
            'name' => 'Gabriel',
            'surname' => 'García Márquez',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Gabriel')
            ->assertJsonPath('data.surname', 'García Márquez');

        $authorId = $response->json('data.id');

        $this->getJson("/api/authors/{$authorId}")
            ->assertOk()
            ->assertJsonPath('data.id', $authorId);

        $this->patchJson("/api/authors/{$authorId}", [
            'surname' => 'García',
        ])
            ->assertOk()
            ->assertJsonPath('data.surname', 'García');

        $this->deleteJson("/api/authors/{$authorId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('authors', ['id' => $authorId]);
    }

    /**
     * Books validate and persist their many-to-many relationships.
     */
    public function test_books_support_crud_and_relationship_resources(): void
    {
        $authors = Author::factory(2)->create();
        $categories = Category::factory(2)->create();

        $this->postJson('/api/books', [
            'name' => 'Cien años de soledad',
            'author_ids' => $authors->modelKeys(),
            'category_ids' => $categories->modelKeys(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Cien años de soledad')
            ->assertJsonCount(2, 'data.authors')
            ->assertJsonCount(2, 'data.categories');

        $book = Book::query()->latest('id')->firstOrFail();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonFragment(['id' => $book->id, 'name' => $book->name]);

        $this->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $book->id);

        $this->patchJson("/api/books/{$book->id}", [
            'name' => 'El amor en los tiempos del cólera',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'El amor en los tiempos del cólera');

        $this->deleteJson("/api/books/{$book->id}")
            ->assertNoContent();
    }

    /**
     * Loan filters and validated ordering parameters work together.
     */
    public function test_loans_can_be_filtered_and_ordered(): void
    {
        $member = Member::factory()->create();
        $otherMember = Member::factory()->create();
        $firstBook = Book::factory()->create();
        $secondBook = Book::factory()->create();

        Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $firstBook->id,
            'loaned_at' => '2026-01-01 10:00:00',
            'due_at' => '2026-01-10 10:00:00',
            'returned_at' => null,
            'active' => true,
        ]);

        Loan::factory()->create([
            'member_id' => $otherMember->id,
            'book_id' => $secondBook->id,
            'loaned_at' => '2026-01-02 10:00:00',
            'due_at' => '2026-01-20 10:00:00',
            'returned_at' => null,
            'active' => true,
        ]);

        Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $firstBook->id,
            'loaned_at' => '2025-12-01 10:00:00',
            'due_at' => '2025-12-15 10:00:00',
            'returned_at' => '2025-12-14 10:00:00',
            'active' => false,
        ]);

        $this->getJson("/api/loans?active=1&member_id={$member->id}&sort=due_at&direction=asc")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.book_id', $firstBook->id)
            ->assertJsonPath('data.0.active', true);

        $this->getJson('/api/loans?sort=unsafe_column')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    /**
     * A loan cannot be duplicated while active and can be returned once.
     */
    public function test_loans_can_be_created_and_returned_safely(): void
    {
        $member = Member::factory()->create();
        $book = Book::factory()->create();

        Loan::factory()->create([
            'member_id' => $member->id,
            'book_id' => $book->id,
            'loaned_at' => '2026-01-01 10:00:00',
            'due_at' => '2026-01-10 10:00:00',
            'returned_at' => null,
            'active' => true,
        ]);

        $this->postJson('/api/loans', [
            'member_id' => $member->id,
            'book_id' => $book->id,
            'loaned_at' => '2026-02-01 10:00:00',
            'due_at' => '2026-02-10 10:00:00',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('book_id');

        $newBook = Book::factory()->create();

        $response = $this->postJson('/api/loans', [
            'member_id' => $member->id,
            'book_id' => $newBook->id,
            'loaned_at' => '2026-02-01 10:00:00',
            'due_at' => '2026-02-10 10:00:00',
        ]);

        $response->assertCreated()->assertJsonPath('data.active', true);
        $loanId = $response->json('data.id');

        $this->postJson("/api/loans/{$loanId}/return")
            ->assertOk()
            ->assertJsonPath('data.active', false)
            ->assertJsonStructure(['data' => ['returned_at']]);

        $this->postJson("/api/loans/{$loanId}/return")
            ->assertStatus(409)
            ->assertJsonPath('message', 'The loan has already been returned.');
    }
}
