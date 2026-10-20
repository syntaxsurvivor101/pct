<?php

namespace App\Services;

use App\Models\Pact;
use Illuminate\Support\Facades\DB;

class PactReadinessService
{
    public function check(Pact $pact): array
    {
        $issues = [];

        if (! in_array($pact->status, ['draft', 'recruiting', 'ready'], true)) {
            $issues[] = 'Pact must be in draft, recruiting or ready status.';
        }

        $rules = $pact->rules;

        if (! $rules) {
            $issues[] = 'Pact rules have not been configured.';
        }
        $activeMembers = $pact->members()->where('status', 'active')->where('is_financial_participant', true)->get();
        $activeMemberIds = $activeMembers->pluck('id');

        if ($activeMembers->count() < $pact->min_members) {
            $issues[] = 'Not enough active financial participants.';
        }

        if ($activeMembers->count() > $pact->max_members) {
            $issues[] = 'Too many active financial participants.';
        }

        if ($rules) {
            $unacceptedCount = $activeMembers->filter( fn ($member) => ! $member->rules_accepted_at || (int) $member->accepted_rules_version !== (int) $rules->version)->count();

            if ($unacceptedCount > 0) {
                $issues[] = "{$unacceptedCount} active financial participant(s) have not accepted the current rules.";
            }
        }

        $slots = $pact->slots()->get();

        if ($slots->count() !== (int) $pact->planned_slots) {
            $issues[] = 'The number of generated payout slots does not match the planned slots.';
        }

        $allocations = DB::table('pact_slot_allocations')->where('pact_id', $pact->id)->get()->groupBy('pact_slot_id');

        $incompleteSlots = 0;
        $invalidAllocations = 0;

        foreach ($slots as $slot) {
            $slotAllocations = $allocations->get($slot->id, collect());

            if ((int) $slotAllocations->sum('allocation_units') !== 10000) {
                $incompleteSlots++;
            }
            foreach ($slotAllocations as $allocation) {
                if (! $activeMemberIds->contains($allocation->pact_member_id)) {
                    $invalidAllocations++;
                }
                if ($rules && ! in_array((int) $allocation->allocation_units, array_map('intval', $rules->permitted_fraction_units ?? [10000]), true)) {
                    $invalidAllocations++;
                }
            }
        }
        if ($incompleteSlots > 0) {
            $issues[] = "{$incompleteSlots} payout slot(s) are not fully allocated.";
        }

        if ($invalidAllocations > 0) {
            $issues[] = "{$invalidAllocations} invalid allocation condition(s) were found.";
        }

        if (! $pact->contribution_amount || $pact->contribution_amount <= 0) {
            $issues[] = "A valid contribution amount is required.";
        }

        if (! $pact->contribution_frequency) {
            $issues[] = "A contribution frequency is required.";
        }

        return [
            'is_ready' => count($issues) === 0,
            'checks' => [
                'active_financial_participants' => $activeMembers->count(),
                'minimum_members' => (int) $pact->min_members,
                'planned_slots' => (int) $pact->planned_slots,
                'generated_slots' => $slots->count(),
                'incomplete_slots' => $incompleteSlots,
                'invalid_allocation_conditions' => $invalidAllocations,
            ],
            'issues' => $issues,
        ];
    }
}