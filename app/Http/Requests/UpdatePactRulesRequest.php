<?php

namespace App\Http\Requests;

use App\Models\Pact;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePactRulesRequest extends FormRequest
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
            'allow_partial_payments' => ['sometimes', 'required', 'boolean'],
            'allow_early_contributions' => ['sometimes', 'required', 'boolean'],
            'allow_advance_payments' => ['sometimes', 'required', 'boolean'],
            'grace_period_hours'  => ['sometimes', 'required', 'integer', 'min:0'],
            'late_penalty_enabled' => ['sometimes', 'required', 'boolean'],
            'late_penalty_type' => ['sometimes', 'nullable', Rule::in(['fixed', 'percentage'])],
            'late_penalty_amount' => ['sometimes', 'nullable', 'integer', 'min:1',],
            'late_penalty_percentage' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100'],
            'payout_order_method' => ['sometimes', 'required', Rule::in(['manual', 'random'])],
            'automatic_reminders_enabled' => ['sometimes', 'required', 'boolean'],
            'reminder_hours_before' => ['sometimes', 'required', 'integer', 'min:0'],
            'overdue_reminders_enabled' => ['sometimes', 'required', 'boolean'],
            'permitted_fraction_units' => ['sometimes', 'nullable', 'array', 'min:1'],
            'permitted_fraction_units.*' => ['integer', 'min:1', 'max:10000', 'distinct',]
        ];
    }


    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()-> isNotEmpty()) {
                    return;
                }
                $pact = $this->route('pact');
                $rules = $pact->rules;

                if (! $rules) {
                    $validator->errors()->add(
                        'rules',
                        'Pact rules could not be found.'
                    );
                    return;
                }
                $enabled = $this->input(
                    'late_penalty_enabled',
                    $rules->late_penalty_enabled
                );
                $type = $this->input(
                    'late_penalty_type',
                    $rules->late_penalty_type
                );
                $amount = $this->input(
                    'late_penalty_amount',
                    $rules->late_penalty_amount
                );
                $percentage = $this->input(
                    'late_penalty_percentage',
                    $rules->late_penalty_percentage
                );

                if ($enabled) {
                    if (! in_array($type, ['fixed', 'percentage'], true)) {
                        $validator->errors()->add(
                            'late_penalty_type',
                            'Select a late penalty type.'
                        );
                    } elseif ($type === 'fixed' && ($amount === null || $amount === '')) {
                        $validator->errors()->add(
                            'late_penalty_amount',
                            'A fixed penalty amount is required.'
                        );
                    } elseif ($type === 'percentage' && ($percentage === null || $percentage === '')) {
                        $validator->errors()->add(
                            'late_penalty_percentage',
                            'A penalty percentage is required'
                        );
                    }
                }
             },
        ];
    }
}
