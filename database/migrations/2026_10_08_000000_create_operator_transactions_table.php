<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operator_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique();
            $table->string('operateur');
            $table->unsignedInteger('montant');
            $table->string('statut')->default('RECU');
            $table->string('operator_reference')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_transactions');
    }
};
