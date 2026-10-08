<?php

namespace App\Http\Controllers;

use App\Models\OperatorTransaction;
use App\Models\Payment;
use App\Services\OperatorService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function result(Request $request)
    {
        $request->validate([
            'reference' => 'required',
            'statut' => 'required|in:REUSSI,ECHEC',
        ]);

        $payment = Payment::where(
            'reference',
            $request->reference
        )->firstOrFail();

        $transaction = OperatorTransaction::where(
            'payment_reference',
            $payment->reference
        )->firstOrFail();

        $data =
            $payment->reference .
            '|' .
            $request->statut .
            '|' .
            $transaction->operator_reference;

        $signature = hash_hmac(
            'sha256',
            $data,
            config('services.operator.secret')
        );

        $paymentService = app(PaymentService::class);

        $paymentService->traiterCallback(
            $payment,
            $request->statut,
            $transaction->operator_reference
        );

        return response()->json([
            'message' => 'Résultat signé envoyé',
            'reference' => $payment->reference,
            'statut' => $request->statut,
            'signature' => $signature,
        ]);
    }

    public function resultWeb(
        Request $request,
        OperatorService $operatorService,
        PaymentService $paymentService
    ) {
        $request->validate([
            'reference' => 'required',
            'statut' => 'required|in:REUSSI,ECHEC',
        ]);

        $payment = Payment::where(
            'reference',
            $request->reference
        )->firstOrFail();

        /*
         * Création/récupération de la transaction
         * auprès de l'opérateur simulé.
         */
        $transaction = $operatorService->recevoirDebit(
            $payment
        );

        /*
         * Enregistrement du résultat du paiement.
         */
        $paymentService->traiterCallback(
            $payment,
            $request->statut,
            $transaction->operator_reference
        );

        return redirect()->route('operator.index');
    }
}
