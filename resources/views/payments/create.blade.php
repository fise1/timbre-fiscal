@extends('layouts.app')

@section('content')

<div class="card">

    <h1>Paiement</h1>

    <p>
        Acte :
        {{ $demande->acte->nom }}
    </p>

    <p>
        Nombre de copies :
        {{ $demande->nombre_copies }}
    </p>

    <h2>
        Montant :
        {{ $demande->montant_total }}
        FCFA
    </h2>

    <form method="POST" action="/payments">

        @csrf

        <input
            type="hidden"
            name="demande_id"
            value="{{ $demande->id }}"
        >

        <label>Téléphone</label>

        <input
            type="text"
            name="telephone"
            placeholder="0101020304"
            required
        >

        <br>

        <label>Opérateur</label>

        <select name="operateur" required>

            <option value="MTN">
                MTN
            </option>

            <option value="MOOV">
                MOOV
            </option>

        </select>

        <br>

        <button type="submit">
            Payer
        </button>

    </form>

</div>

@endsection