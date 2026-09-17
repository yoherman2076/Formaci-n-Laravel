<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $books = Book::query()
            ->with(['authors', 'categories'])
            ->paginate();

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $data = $request->validated();
        $authorIds = $data['author_ids'];
        $categoryIds = $data['category_ids'];

        unset($data['author_ids'], $data['category_ids']);

        $book = Book::create($data);
        $book->authors()->sync($authorIds);
        $book->categories()->sync($categoryIds);

        return (new BookResource($book->load(['authors', 'categories'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): BookResource
    {
        return new BookResource($book->load(['authors', 'categories']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateBookRequest $request,
        Book $book
    ): BookResource {
        $data = $request->validated();
        $authorIds = $data['author_ids'] ?? null;
        $categoryIds = $data['category_ids'] ?? null;

        unset($data['author_ids'], $data['category_ids']);

        $book->update($data);

        if ($authorIds !== null) {
            $book->authors()->sync($authorIds);
        }

        if ($categoryIds !== null) {
            $book->categories()->sync($categoryIds);
        }

        return new BookResource($book->load(['authors', 'categories']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): Response
    {
        $book->delete();

        return response()->noContent();
    }
}
