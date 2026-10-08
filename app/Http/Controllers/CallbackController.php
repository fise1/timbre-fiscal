<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function handle(
        Request $request,
        PaymentService $paymentService
    ) {
        $request->validate([
            'reference' => 'required',
            'statut' => 'required|in:REUSSI,ECHEC',
            'operateur_reference' => 'required',
            'signature' => 'required',
        ]);

        $payment = Payment::where(
            'reference',
            $request->reference
        )->firstOrFail();

        $data =
            $payment->reference .
            '|' .
            $request->statut .
            '|' .
            $request->operateur_reference;

        $expected = hash_hmac(
            'sha256',
            $data,
            config('services.operator.secret')
        );

        if (!hash_equals(
            $expected,
            $request->signature
        )) {
            return response()->json([
                'message' => 'Signature invalide'
            ], 401);
        }

        $paymentService->traiterCallback(
            $payment,
            $request->statut,
            $request->operateur_reference
        );

        return response()->json([
            'message' => 'Résultat enregistré'
        ]);
    }
}