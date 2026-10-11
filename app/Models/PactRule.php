<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PactRule extends Model
{
    protected $fillable = [
        'pact_id', 'allow_partial_payments', 'allow_early_contributions', 'allow_advance_payments', 'grace_period_hours', 'late_penalty_enabled', 'payout_order_method', 'automatic_reminders_enabled', 'reminder_hours_before', 'overdue_reminders_enabled', 'permitted_fraction_units', 'version',

    ];

    protected function casts(): array
    {
        return ['permitted_fraction_units' => 'array'];
    }
}