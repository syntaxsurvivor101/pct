<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pact;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PactScheduleController extends Controller
{
    public function generate(Request $request, Pact $pact): JsonResponse
    {
        if ((int) $pact->organiser_id !== (int) $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorised to generate this schedule.',
            ], 403);
        }

        return DB::transaction(function () use ($pact) {
            $lockedPact = Pact::query()->lockForUpdate()->findOrFail($pact->id);

            if ($lockedPact->status !== 'draft') {
                return response()->json([
                    'message' => 'Schedules can only be generated for draft Pacts.',

                ], 409);
            }

            if (! $lockedPact->start_date) {
                return response()->json([
                    'message' => 'Set the Pact start date first.',
                ], 422);
            }

            $slots = $lockedPact->slots()->orderBy('position')->get();

            if ($slots->isEmpty() || $slots->count() !== (int) $lockedPact->planned_slots) {
                return response()->json([
                    'message' => 'Generate all planned payout slots first.',

                ], 409);
            }

            if ($slots->contains(fn ($slot) => $slot->scheduled_date !== null)) {
                return response()->json([
                    'message' => 'This Pact already has a generated schedule.',

                ], 409);
            }

            $allocatedUnits = DB::table('pact_slot_allocations')->where('pact_id', $lockedPact->id)->select('pact_slot_id')->selectRaw('SUM(allocation_units) as total_units')->groupBy('pact_slot_id')->pluck('total_units', 'pact_slot_id');

            foreach ($slots as $slot) {
                if ((int) $allocatedUnits->get($slot->id, 0) !== 1000) {
                    return response()->json([
                        'message' => 'All payout slots must be fully allocated before scheduling.',
                    ], 409);
                }
            }

            $startDate = Carbon::parse($lockedPact->start_date)->startOfDay();
            $frequency = $lockedPact->contribution_frequency;

            if (! in_array($frequency, ['every_5_days', 'weekly', 'biweekly', 'monthly'], true)) {
                return response()->json([
                    'message' => 'Unsupported contribution frequency.',
                ], 422);
            }

            foreach ($slots as $index => $slot) {
                $date = $startDate->copy();
                switch ($frequency) {
                    case 'every_5_days':
                        $date->addDays($index * 5);
                        break;

                    case 'weekly':
                        $date->addWeeks($index);
                        break;

                    case 'biweekly':
                        $date->addWeeks($index * 2);
                        break;

                    case 'monthly':
                        $date->addMonthsNoOverflow($index);
                        break;
                }
                $slot->update([
                    'scheduled_date' => $date->toDateString(),
                    'status' => 'scheduled',

                ]);
            }

            return response()->json([
                'message' => 'Payout schedule generated successfully.',
                'schedule_version' => $lockedPact->schedule_version,
                'data' => $lockedPact->slots()->orderBy('position')->get(),
            ], 200);
        });
    }
}