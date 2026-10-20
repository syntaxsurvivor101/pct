<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PactController;
use App\Http\Controllers\Api\PactMemberController;
use App\Http\Controllers\Api\PactSlotController;
use App\Http\Controllers\Api\PactSlotAllocationController;
use App\Http\Controllers\Api\PactReadinessController;
use App\Http\Controllers\Api\PactScheduleController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/pacts', [PactController::class, 'store']);

    Route::get('/pacts', [PactController::class, 'index']);
    Route::get('/pacts/{pact}', [PactController::class, 'show']);
    Route::patch('/pacts/{pact}', [PactController::class, 'update']);
    Route::patch('/pacts/{pact}/rules', [PactController::class, 'updateRules']);
    Route::post('/pacts/{pact}/members', [PactMemberController::class, 'store']);
    Route::post('/pacts/{pact}/accept', [PactMemberController::class, 'accept']);

    Route::post('/pacts/{pact}/slots/generate', [PactSlotController::class, 'generate']);
    Route::post('/pacts/{pact}/slots/{slot}/allocations', [PactSlotAllocationController::class, 'store']);

    Route::get('/pacts/{pact}/slots', [PactSlotController::class, 'index']);
    Route::get('/pacts/{pact}/readiness', [PactReadinessController::class, 'show']);

    Route::post('/pacts/{pact}/schedule/generate', [PactScheduleController::class, 'generate']);
});