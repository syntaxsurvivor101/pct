<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StorePactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'currency' => ['required', Rule::in(['GBP', 'NGN'])],
            'contribution_amount' => ['required', 'integer', 'min:1'],
            'contribution_frequency' => ['required', Rule::in(['every_5_days', 'weekly', 'biweekly', 'monthly'])],
            'collection_method' => ['required', Rule::in(['direct_to_recipient', 'via_organiser'])],
            'visibility' => ['sometimes', Rule::in(['private', 'public'])],
            'join_method' => ['sometimes', Rule::in(['automatic', 'approval_required'])],
            'activation_method' => ['sometimes', Rule::in(['manual', 'automatic'])],
            'min_members' => ['sometimes', 'integer', 'min:3', 'max:30'],
            'max_members' => ['sometimes', 'integer', 'min:3', 'max:30'],
            'planned_slots' => ['required', 'integer', 'min:1'],
            'max_slots_per_member' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],

        ];
    }
}