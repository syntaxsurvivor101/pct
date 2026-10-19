<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePactMemberRequest;
use App\Http\Requests\AcceptPactRequest;
use App\Models\Pact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class PactMemberController extends Controller
{
    public function store(StorePactMemberRequest $request, Pact $pact): JsonResponse
    {
        if ($pact->status !== 'draft') {
            return response()->json([
                'message' => 'Members can only be added while the Pact is in draft.',
            ], 409);
        }

        $data = $request->validated();

        $member = DB::transaction(function () use ($pact, $data){
            $lockedPact = Pact::query()->lockForUpdate()->findOrFail($pact->id);

            if ($lockedPact->status !== 'draft') {
                abort(409, 'Members can only be added while the Pact is in draft.');
            }

            $memberCount = $lockedPact->members()->whereIn('status', ['pending', 'active', 'suspended'])->count();

            if ($memberCount >= $lockedPact->max_members) {
                abort(409, 'This Pact has reached its maximum number of members.');
            }

            return $lockedPact->members()->create([
                'user_id' => $data['user_id'],
                'role' => 'member',
                'is_financial_participant' => $data['is_financial_participant'],
                'status' => 'pending',
            ]);

        });

        return response()->json([
            'message' => 'Pact member added successfully.',
            'data' => $member,

        ], 201);
    }

    public function accept(AcceptPactRequest $request, Pact $pact): JsonResponse
    {
        $member = DB::transaction(function () use ($request, $pact) {
            $lockedPact = Pact::query()->lockForUpdate()->findOrFail($pact->id);

            if (! in_array($lockedPact->status, ['draft', 'recruiting'], true)) {
                abort(409, 'This Pact is not accepting members.');
            }

            $member = $lockedPact->members()->where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();

            if ($member->status !== 'pending') {
                abort(409, 'This membership is no longer pending.');
            }

            $rules = $lockedPact->rules;
            if (! $rules) {
                abort(409, 'Pact rules are missing.');
            }

            $member->update([
                'status' => 'active',
                'rules_accepted_at' => now(),
                'accepted_rules_version' => $rules->version,
                'joined_at' => now(),
            ]);

            return $member->fresh();
        });

        return response()->json([
            'message' => 'Pact membership accepted successfully.',
            'data' => $member,
        ]);
    }
}