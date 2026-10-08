<?php

namespace App\Http\Controllers;

use App\Models\Acte;
use App\Models\User;
use App\Services\DemandeService;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
    public function store(
        Request $request,
        DemandeService $service
    ) {
        $request->validate([
            'acte_id' => ['required', 'exists:actes,id'],
            'nombre_copies' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::findOrFail(
            $request->header('X-User-Id')
        );

        $acte = Acte::findOrFail($request->acte_id);

        $demande = $service->creer(
            $user,
            $acte,
            $request->nombre_copies
        );

        return response()->json([
            'message' => 'Demande créée',
            'demande' => $demande,
        ], 201);
    }

    public function storeWeb(
        Request $request,
        DemandeService $service
    ) {
        $request->validate([
            'acte_id' => ['required', 'exists:actes,id'],
            'nombre_copies' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::where(
            'email',
            'test@example.com'
        )->firstOrFail();

        $acte = Acte::findOrFail($request->acte_id);

        $demande = $service->creer(
            $user,
            $acte,
            $request->nombre_copies
        );

        return redirect()->route(
            'payments.show',
            $demande->id
        );
    }
}
