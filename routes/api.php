<?php

use App\Http\Controllers\Api\UserSubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas para gerenciar as assinaturas do usuário
Route::apiResource('subscriptions', UserSubscriptionController::class);
