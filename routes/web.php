<?php

use App\Http\Controllers\DemandeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OperatorController;
use App\Models\Acte;
use App\Models\Payment;
use Illuminate\Support\Facades\Route;

Route::get('/demande', function () {
    return view('demandes.create', [
        'actes' => Acte::all(),
    ]);
})->name('demande.create');

Route::post('/demandes', [
    DemandeController::class,
    'storeWeb',
])->name('demandes.store');

Route::get('/payments/{id}', [
    PaymentController::class,
    'show',
])->name('payments.show');

Route::post('/payments', [
    PaymentController::class,
    'storeWeb',
])->name('payments.store');

Route::get('/operator', function () {
    return view('operator.index', [
        'payments' => Payment::latest()->get(),
    ]);
})->name('operator.index');

Route::post('/operator/result', [
    OperatorController::class,
    'resultWeb',
])->name('operator.result');