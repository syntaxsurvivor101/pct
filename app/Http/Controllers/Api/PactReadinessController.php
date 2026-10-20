<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pact;
use App\Services\PactReadinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PactReadinessController extends Controller
{
    public function show(Request $request, Pact $pact, PactReadinessService $readinessService): JsonResponse
    {
        $userId = $request->user()->id;

        $isOrganiser = (int) $pact->organiser_id === (int) $userId;

        $isMember = $pact->members()->where('user_id', $userId)->whereIn('status', ['pending', 'active', 'suspended'])->exists();

        if (! $isOrganiser && ! $isMember) {
            return response()->json([
                'message' => 'Pact not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Pact readiness checked successfully.',
            'data' => $readinessService->check($pact),
        ]);
    }
}