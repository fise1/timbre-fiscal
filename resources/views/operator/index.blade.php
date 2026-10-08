@extends('layouts.app')

@section('content')

<div class="card">

    <h1>Opérateur de paiement simulé</h1>

    <p>
        Cette page simule MTN / MOOV.
    </p>

    @if ($payments->isEmpty())

    <p>Aucun paiement en attente.</p>

    @else

    @foreach ($payments as $payment)

    <hr>

    <p>
        <strong>Référence :</strong>
        {{ $payment->reference }}
    </p>

    <p>
        <strong>Montant :</strong>
        {{ $payment->montant }} FCFA
    </p>

    <p>
        <strong>Opérateur :</strong>
        {{ $payment->operateur }}
    </p>

    <p>
        <strong>Téléphone :</strong>
        {{ $payment->telephone }}
    </p>

    <p>
        <strong>Statut :</strong>
        {{ $payment->statut }}
    </p>

    @if ($payment->statut === 'EN_ATTENTE')

    <form
        method="POST"
        action="{{ route('operator.result') }}">
        @csrf

        <input
            type="hidden"
            name="reference"
            value="{{ $payment->reference }}">

        <input
            type="hidden"
            name="statut"
            value="REUSSI">

        <button type="submit">Accepter</button>
    </form>

    <form
        method="POST"
        action="{{ route('operator.result') }}">
        @csrf

        <input
            type="hidden"
            name="reference"
            value="{{ $payment->reference }}">

        <input
            type="hidden"
            name="statut"
            value="ECHEC">

        <button type="submit">Refuser</button>
    </form>
    @endif

    @endforeach

    @endif

</div>

@endsection