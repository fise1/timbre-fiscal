<?php

namespace App\Http\Controllers;

use App\Services\OperatorService;
use App\Models\Demande;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(
        Request $request,
        PaymentService $service
    ) {
        $request->validate([
            'demande_id' => [
                'required',
                'exists:demandes,id',
            ],

            'telephone' => [
                'required',
                'regex:/^01[0-9]{8}$/',
            ],

            'operateur' => [
                'required',
                'in:MTN,MOOV',
            ],
        ]);

        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json([
                'message' => 'Idempotency-Key obligatoire'
            ], 400);
        }

        $user = User::findOrFail(
            $request->header('X-User-Id')
        );

        $demande = Demande::findOrFail(
            $request->demande_id
        );

        $payment = $service->creer(
            $user,
            $demande,
            $request->telephone,
            $request->operateur,
            $idempotencyKey
        );

        return response()->json([
            'message' => 'Paiement créé',
            'payment' => $payment,
        ], 201);
    }

    public function launch(
        int $id,
        OperatorService $operatorService
    ) {
        $payment = \App\Models\Payment::findOrFail($id);

        $transaction = $operatorService->recevoirDebit($payment);

        return response()->json([
            'message' => 'Débit reçu par l’opérateur',
            'reference' => $payment->reference,
            'operator_reference' => $transaction->operator_reference,
            'statut' => 'RECU',
        ]);
    }
    public function show(int $id)
    {
        $demande = \App\Models\Demande::with('acte')
            ->findOrFail($id);

        return view('payments.create', [
            'demande' => $demande
        ]);
    }
    public function storeWeb(
        Request $request,
        \App\Services\PaymentService $service
    ) {
        $request->validate([
            'demande_id' => ['required', 'exists:demandes,id'],
            'telephone' => [
                'required',
                'regex:/^01[0-9]{8}$/'
            ],
            'operateur' => [
                'required',
                'in:MTN,MOOV'
            ],
        ]);

        $user = \App\Models\User::where(
            'email',
            'test@example.com'
        )->firstOrFail();

        $demande = \App\Models\Demande::findOrFail(
            $request->demande_id
        );

        $idempotencyKey = \Illuminate\Support\Str::uuid();

        $payment = $service->creer(
            $user,
            $demande,
            $request->telephone,
            $request->operateur,
            $idempotencyKey
        );

        return redirect()->route('operator.index');
    }
}
