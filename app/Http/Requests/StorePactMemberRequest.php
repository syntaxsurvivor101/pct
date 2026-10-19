<?php

namespace App\Http\Requests;

use App\Models\Pact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePactMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pact = $this->route('pact');

        return $pact instanceof Pact && $this->user() !== null && (int) $pact->organiser_id === (int) $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pact = $this->route('pact');
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('pact_members', 'user_id')->where('pact_id', $pact->id)],
            'is_financial_participant' => ['required', 'boolean'],
        ];
    }
}
