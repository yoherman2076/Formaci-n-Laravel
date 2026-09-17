<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer', 'exists:members,id'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'loaned_at' => ['required', 'date'],
            'due_at' => ['required', 'date', 'after:loaned_at'],
        ];
    }

    /**
     * Add validation for the book's active loan state.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('book_id')) {
                return;
            }

            $hasActiveLoan = Loan::query()
                ->where('book_id', (int) $this->input('book_id'))
                ->where('active', true)
                ->exists();

            if ($hasActiveLoan) {
                $validator->errors()->add(
                    'book_id',
                    'The selected book already has an active loan.'
                );
            }
        });
    }
}
