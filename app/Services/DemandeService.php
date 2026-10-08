<?php

namespace App\Services;

use App\Models\Acte;
use App\Models\Demande;
use App\Models\User;

class DemandeService
{
    private const FRAIS_SERVICE = 100;

    public function creer(
        User $user,
        Acte $acte,
        int $nombreCopies
    ): Demande {
        $montant = (
            $acte->tarif_unitaire * $nombreCopies
        ) + self::FRAIS_SERVICE;

        return Demande::create([
            'user_id' => $user->id,
            'acte_id' => $acte->id,
            'nombre_copies' => $nombreCopies,
            'montant_total' => $montant,
        ]);
    }
}