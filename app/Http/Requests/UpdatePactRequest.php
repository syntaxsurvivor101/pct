<?php

namespace App\Http\Requests;

use App\Models\Pact;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePactRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'nullable', 'string'],
            'currency' => ['sometimes', 'required', Rule::in(['GBP', 'NGN'])],
            'contribution_amount' => ['sometimes', 'required', 'integer', 'min:1'],
            'contribution_frequency' => ['sometimes', 'required', Rule::in(['every_5_days', 'weekly', 'biweekly', 'monthly']),],
            'collection_method' => ['sometimes', 'required', Rule::in(['direct_to_recipient', 'via_organiser']),],
            'visibility' => ['sometimes', 'required', Rule::in(['private', 'public'])],
            'join_method' => ['sometimes', 'required', Rule::in(['manual','approval_required']),],
            'activation_method' => ['sometimes', 'required', Rule::in(['manual', 'automatic'])],
            'min_members' => ['sometimes', 'required', 'integer', 'min:3', 'max:30'],
            'max_members' => ['sometimes', 'required', 'integer', 'min:3', 'max:30'],
            'planned_slots' => ['sometimes', 'required', 'integer', 'min:3', 'max:30'],
            'max_slots_per_member' => ['sometimes', 'required', 'integer', 'min:3', 'max:30'],
            'start_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pact = $this->route('pact');
                $min = (int) $this->input('min_members', $pact->min_members);
                $max = (int) $this->input('max_members', $pact->max_members);

                if ($min > $max) {
                    $validator->errors()->add(
                        'min_members',
                        'Minimum members cannot exceed maximum members.'
                    );
                }
                $maxSlots = (int) $this->input('max_slots_per_member', $pact->max_slots_per_member);
                $plannedSlots = (int) $this->input('planned_slots', $pact->planned_slots);

                if($maxSlots > $plannedSlots) {
                    $validator->errors()->add('max_slots_per_member', 'Maximum slots per member cannot exceed planned slots');
                }
            },


        ];
    }
}
