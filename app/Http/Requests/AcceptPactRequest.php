<?php

namespace App\Http\Requests;

use App\Models\Pact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcceptPactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pact = $this->route('pact');
        return $pact instanceof Pact && $this->user() !== null && $pact->members()->where('user_id', $this->user()->id)->where('status', 'pending')->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'accept_rules' => ['required', 'accepted'],
        ];
    }
}