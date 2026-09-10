<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $authors = Author::query()
            ->with('books')
            ->paginate();

        return AuthorResource::collection($authors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthorRequest $request): JsonResponse
    {
        $author = Author::create($request->validated());

        return (new AuthorResource($author->load('books')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author): AuthorResource
    {
        return new AuthorResource($author->load('books'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        UpdateAuthorRequest $request,
        Author $author
    ): AuthorResource {
        $author->update($request->validated());

        return new AuthorResource($author->load('books'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author): Response
    {
        $author->delete();

        return response()->noContent();
    }
}
