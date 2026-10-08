<?php

namespace Database\Seeders;

use App\Models\Acte;
use Illuminate\Database\Seeder;

class ActeSeeder extends Seeder
{
    public function run(): void
    {
        Acte::create([
            'nom' => 'Acte de naissance',
            'tarif_unitaire' => 1000,
        ]);

        Acte::create([
            'nom' => 'Casier judiciaire',
            'tarif_unitaire' => 1500,
        ]);

        Acte::create([
            'nom' => 'Certificat de résidence',
            'tarif_unitaire' => 500,
        ]);
    }
}
