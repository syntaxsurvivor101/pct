<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePactSlotAllocationRequest;
use App\Models\Pact;
use App\Models\PactSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PactSlotAllocationController extends Controller
{
    public function store(StorePactSlotAllocationRequest $request, Pact $pact, PactSlot $slot): JsonResponse {
        $data = $request->validated();

        return DB::transaction(function () use ($pact, $slot, $data) {
            $lockedPact = Pact::query()->lockForUpdate()->findOrFail($pact->id);
            if ($lockedPact->status !== 'draft') {
                return response()->json([
                    'message' => 'Allocations can only be changed for draft Pacts.',
                ], 409);
            }

            if ((int) $slot->pact_id !== (int) $lockedPact->id) {
                return response()->json([
                    'message' => 'Slot not found for this Pact.',
                ], 404);
            }

            $member = $lockedPact->members()->whereKey($data['pact_member_id'])->where('status', 'active')->where('is_financial_participant', true)->first();
            if (! $member) {
                return response()->json([
                    'message' => 'An active financial participant is required.',
                ], 422);
            }


            $permittedUnits = $lockedPact->rules?->permitted_fraction_units ?? [10000];

            if (! in_array((int) $data['allocation_units'], $permittedUnits, true)) {
                return response()->json([
                    'message' => 'The allocation amount is not permitted by this Pact.',
                    'permitted_fraction_units' => $permittedUnits,

                ], 422);
            }

            $existingAllocation = DB::table('pact_slot_allocations')->where('pact_slot_id', $slot->id)->where('pact_member_id', $member->id)->exists();
            if ($existingAllocation) {
                return response()->json([
                    'message' => 'This member already has an allocation in this slot.',
                ], 409);
            }

            $allocatedUnits = DB::table('pact_slot_allocations')->where('pact_slot_id', $slot->id)->sum('allocation_units');

            if ($allocatedUnits + $data['allocation_units'] > 10000) {
                return response()->json([
                    'message' => 'This allocation would exceed 100% of the slot.',
                ], 422);
            }

            $memberTotalUnits = DB::table('pact_slot_allocations')->where('pact_id', $lockedPact->id)->where('pact_member_id', $member->id)->sum('allocation_units');

            if ($memberTotalUnits + $data['allocation_units'] > $lockedPact->max_slots_per_member * 10000) {
                return response()->json([
                    'message' => 'This member would exceed the maximum slot allocation.',
                ], 422);
            }

            $allocation = DB::table('pact_slot_allocations')->insertGetId([
                'pact_id' => $lockedPact->id,
                'pact_slot_id' => $slot->id,
                'pact_member_id' => $member->id,
                'allocation_units' => $data['allocation_units'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Slot allocation created successfully',
                'data' => DB::table('pact_slot_allocations')->where('id', $allocation)->first(),
            ], 201);
        });
    }
}