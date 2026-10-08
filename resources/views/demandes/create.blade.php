<form method="POST" action="{{ route('demandes.store') }}">

    @csrf

    <label for="acte_id">
        Type d'acte
    </label>

    <select id="acte_id" name="acte_id" required>
        @foreach ($actes as $acte)
            <option value="{{ $acte->id }}">
                {{ $acte->nom }} - {{ $acte->tarif_unitaire }} FCFA
            </option>
        @endforeach
    </select>

    <br><br>

    <label for="nombre_copies">
        Nombre de copies
    </label>

    <input
        id="nombre_copies"
        type="number"
        name="nombre_copies"
        min="1"
        value="1"
        required
    >

    <br><br>

    <button type="submit">
        Continuer
    </button>

</form>