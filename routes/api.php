<?php

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/demandes', [
    DemandeController::class,
    'store'
]);

Route::post('/payments', [
    PaymentController::class,
    'store'
]);

Route::post('/payments/{id}/launch', [
    PaymentController::class,
    'launch'
]);

Route::post('/operator/callback', [
    CallbackController::class,
    'handle'
]);

Route::post('/operator/result', [
    OperatorController::class,
    'result'
]);