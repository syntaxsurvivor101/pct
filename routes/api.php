<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PactController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/pacts', [PactController::class, 'store']);

    Route::get('/pacts', [PactController::class, 'index']);
    Route::get('/pacts/{pact}', [PactController::class, 'show']);
    Route::patch('/pacts/{pact}', [PactController::class, 'update']);
});