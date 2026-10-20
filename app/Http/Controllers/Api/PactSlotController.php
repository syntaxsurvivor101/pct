<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PactSlotController extends Controller
{
    public function index(Request $request, Pact $pact): JsonResponse
    {
        $userId = $request->user()->id;

        $isOrganiser = (int) $pact->organiser_id === (int) $userId;

        $isMember = $pact->members()->where('user_id', $userId)->whereIn('status', ['pending', 'active', 'suspended'])->exists();

        if (! $isOrganiser && ! $isMember) {
            return response()->json([
                'message' => 'Pact not found.',
            ], 404);
        }

        $slots = $pact->slots()->orderBy('position')->get();
        $allocations = DB::table('pact_slot_allocations')->where('pact_id', $pact->id)->get()->groupBy('pact_slot_id');
        $data = $slots->map(function ($slot) use ($allocations) {
            $slotAllocations = $allocations->get($slot->id, collect());

            $allocatedUnits = (int) $slotAllocations->sum('allocation_units');

            return [
                'id' => $slot->id,
                'position' => $slot->position,
                'status' => $slot->status,
                'scheduled_date' => $slot->scheduled_date,
                'allocated_units' => $allocatedUnits,
                'remaining_units' => 10000 - $allocatedUnits,
                'allocated_percentage' => $allocatedUnits / 100,
                'is_fully_allocated' => $allocatedUnits === 10000,
                'allocations' => $slotAllocations->map(fn ($allocation) => [
                    'id' => $allocation->id,
                    'pact_member_id' => $allocation->pact_member_id,
                    'allocation_units' => (int) $allocation->allocation_units,
                    'percentage' => $allocation->allocation_units / 100,
                ])->values(),
            ];
        });

        return response()->json([
            'message' => 'Pact slots retrieved successfully.',
            'data' => $data,
        ]);
    }

    public function generate(Request $request, Pact $pact): JsonResponse
    {
        if ((int) $pact->organiser_id !== (int) $request->user()->id)
        {
            return response()->json([
                'message' => 'You are not authorised to generate slots.',
            ], 403);
        }

        return DB::transaction(function () use ($pact) {
            $lockedPact = Pact::query()->lockForUpdate()->findOrFail($pact->id);
            if ($lockedPact->status !== 'draft') {
                return response()->json([
                    'message' => 'Slots can only be generated for draft Pacts.',
                ], 409);
            }

            if ($lockedPact->slots()->exists()) {
                return response()->json([
                    'message' => 'Slots have already been generated for this Pact.',
                ], 409);
            }

            for ($position = 1; $position <= $lockedPact->planned_slots; $position++) {
                $lockedPact->slots()->create([
                    'position' => $position,
                    'status' => 'pending',
                ]);
            }

            return response()->json([
                'message' => 'Pact slots generated successfully.',
                'data' => $lockedPact->slots()->orderBy('position')->get(),

            ], 201);
        });
    }
}