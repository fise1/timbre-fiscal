<?php

namespace App\Services;

use App\Models\OperatorTransaction;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OperatorService
{
    public function recevoirDebit(
        Payment $payment
    ): OperatorTransaction {

        return DB::transaction(function () use ($payment) {

            $transaction =
                OperatorTransaction::where(
                    'payment_reference',
                    $payment->reference
                )->first();

            if ($transaction) {
                return $transaction;
            }

            return OperatorTransaction::create([
                'payment_reference' => $payment->reference,
                'operateur' => $payment->operateur,
                'montant' => $payment->montant,
                'statut' => 'RECU',
                'operator_reference' =>
                    'OP-' . Str::uuid(),
            ]);
        });
    }
}