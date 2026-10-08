<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('demande_id')
                ->constrained('demandes')
                ->cascadeOnDelete();

            $table->string('reference')->unique();
            $table->string('idempotency_key')->unique();
            $table->string('telephone');

            $table->enum('operateur', [
                'MTN',
                'MOOV'
            ]);

            $table->unsignedInteger('montant');

            $table->enum('statut', [
                'EN_ATTENTE',
                'REUSSI',
                'ECHEC',
                'EXPIRE'
            ])->default('EN_ATTENTE');

            $table->string('operateur_reference')->nullable();
            $table->timestamp('callback_received_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};