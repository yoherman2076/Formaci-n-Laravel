<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'book_id' => $this->book_id,
            'loaned_at' => $this->loaned_at?->toISOString(),
            'due_at' => $this->due_at?->toISOString(),
            'returned_at' => $this->returned_at?->toISOString(),
            'active' => $this->active,
            'member' => $this->whenLoaded('member', fn (): array => [
                'id' => $this->member->id,
                'name' => $this->member->name,
                'surname' => $this->member->surname,
            ]),
            'book' => $this->whenLoaded('book', fn (): array => [
                'id' => $this->book->id,
                'name' => $this->book->name,
            ]),
        ];
    }
}
