<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePactRequest;
use App\Http\Requests\UpdatePactRequest;
use App\Models\Pact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $pacts = Pact::query()
            ->where(function ($query) use ($userId) {
                $query->where('organiser_id', $userId)
                    ->orWhereHas('members', function($memberQuery) use ($userId) {
                        $memberQuery->where('user_id', $userId)
                            ->whereIn('status', ['pending', 'active', 'suspended']);
                    });
            })->with('rules')->latest()->paginate(15);

            return response()->json([
                'message' => 'Pacts retrieved successfully.',
                'data' => $pacts,
            ]);
    }



    public function show(Request $request, Pact $pact): JsonResponse
    {
        $userId = $request->user()->id;

        $isOrganiser = $pact->organiser_id === $userId;

        $isMember = $pact->members()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'active', 'suspended'])->exists();

        if (! $isOrganiser && ! $isMember){
            abort(404);
        }

        $pact->load([
            'rules', 
            'members' => function ($query){
                $query->select([
                    'id', 
                    'pact_id', 
                    'user_id', 
                    'role', 
                    'is_financial_participant', 
                    'status', 
                    'joined_at'
                ]);
             },
            'slots' => function ($query){
                $query->orderBy('position');
            },
        ]);

        return response()->json([
            'message' => 'Pact retrieved successfully.',
            'data' => $pact,

        ]);
    }


    public function store(StorePactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $pact = DB::transaction(function () use ($request, $data) {

            $pact = Pact::create([
                ...$data,
                'organiser_id' => $request->user()->id,
                'type' => 'rosca',
                'status' => 'draft',
            ]);

            $pact->members()->create([
                'user_id' => $request->user()->id,
                'role' => 'organiser',
                'is_financial_participant' => false,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $pact->rules()->create([

                'allow_partial_payments' => false,
                'allow_early_contributions' => false,
                'allow_advance_payments' => false,
                'grace_period_hours' => 0,
                'late_penalty_enabled' => false,
                'payout_order_method' => 'manual',
                'automatic_reminders_enabled' => true,
                'reminder_hours_before' => 24,
                'overdue_reminders_enabled' => true,
                'permitted_fraction_units' => [10000],
                'version' => 1,
            ]);

            return $pact;
        });

        return response()->json([
            'message' => 'Pact created successfully',
            'data' => $pact->load(['rules']),
        ], 201);
    }

    public function update(UpdatePactRequest $request, Pact $pact): JsonResponse
    {

        if ($pact->status !== 'draft'){
            return response()->json([
                'message' => 'Only draft Pacts can be updated.',
            ], 409);
        }

        $pact->update($request->validated());

        return response()->json([
            'message' => 'Pact updated successfully.',
            'data' => $pact->fresh(['rules']),

        ]);
    }

}