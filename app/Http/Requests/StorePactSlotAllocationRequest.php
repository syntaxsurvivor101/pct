<?php

namespace App\Http\Requests;

use App\Models\Pact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePactSlotAllocationRequest extends FormRequest
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
        return [
            'pact_member_id' => [
                'required',
                'integer',
                'exists:pact_members,id',
            ],
            'allocation_units' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],
        ];
    }
}
