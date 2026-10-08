<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function creer(
        User $user,
        Demande $demande,
        string $telephone,
        string $operateur,
        string $idempotencyKey
    ): Payment {

        return DB::transaction(function () use (
            $user,
            $demande,
            $telephone,
            $operateur,
            $idempotencyKey
        ) {

            if ($demande->user_id !== $user->id) {
                abort(403, 'Demande interdite.');
            }

            $ancien = Payment::where(
                'idempotency_key',
                $idempotencyKey
            )->first();

            if ($ancien) {
                return $ancien;
            }

            return Payment::create([
                'user_id' => $user->id,
                'demande_id' => $demande->id,
                'reference' => 'PAY-' . Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'telephone' => $telephone,
                'operateur' => $operateur,
                'montant' => $demande->montant_total,
                'statut' => 'EN_ATTENTE',
            ]);
        });
    }

    public function traiterCallback(
        Payment $payment,
        string $statut,
        string $operatorReference
    ): void {

        DB::transaction(function () use (
            $payment,
            $statut,
            $operatorReference
        ) {

            $payment->refresh();

            if (in_array($payment->statut, [
                'REUSSI',
                'ECHEC',
                'EXPIRE'
            ])) {
                return;
            }

            $payment->update([
                'statut' => $statut,
                'operateur_reference' => $operatorReference,
                'callback_received_at' => now(),
            ]);
        });
    }
}
