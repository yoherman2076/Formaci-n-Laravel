<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoanIndexRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LoanController extends Controller
{
    /**
     * Display a filtered and ordered listing of loans.
     */
    public function index(LoanIndexRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $query = Loan::query()->with(['member', 'book']);

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if (isset($data['member_id'])) {
            $query->where('member_id', $data['member_id']);
        }

        $sort = $data['sort'] ?? 'due_at';
        $direction = $data['direction'] ?? 'asc';

        return LoanResource::collection(
            $query
                ->orderBy($sort, $direction)
                ->paginate()
        );
    }

    /**
     * Store a newly created loan.
     */
    public function store(StoreLoanRequest $request): JsonResponse
    {
        $loan = Loan::create([
            ...$request->validated(),
            'returned_at' => null,
            'active' => true,
        ]);

        return (new LoanResource($loan->load(['member', 'book'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Mark a loan as returned.
     */
    public function returnBook(Loan $loan): LoanResource|JsonResponse
    {
        if (! $loan->active) {
            return response()->json([
                'message' => 'The loan has already been returned.',
            ], Response::HTTP_CONFLICT);
        }

        $loan->update([
            'returned_at' => now(),
            'active' => false,
        ]);

        return new LoanResource($loan->load(['member', 'book']));
    }
}
