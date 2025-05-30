<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use Illuminate\Http\Request;

class CandidatureController extends Controller
{
    /**
     * Liste toutes les candidatures (avec les relations si besoin).
     */
    public function index()
    {
        // Tu peux inclure les relations si tu veux : formation et candidat
        $candidatures = Candidature::with(['formation', 'candidat'])->paginate(15);
        return response()->json($candidatures, 200);
    }

    /**
     * Enregistre une nouvelle candidature, après vérification.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'id_formation' => 'required|exists:formations,id',
            'id_candidat' => 'required|exists:candidats,id',
            'statut' => 'required|in:en attente,acceptée,refusée',
        ]);

        // Vérification d'une candidature existante
        $existing = Candidature::where('id_formation', $validated['id_formation'])
            ->where('id_candidat', $validated['id_candidat'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Candidature déjà existante pour cette formation',
                'candidature' => $existing
            ], 200);
        }

        // Création de la candidature
        $candidature = Candidature::create($validated);
        return response()->json($candidature, 201);
    }

    /**
     * Affiche une candidature par ID.
     */
    public function show($id)
    {
        $candidature = Candidature::with(['formation', 'candidat'])->find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        return response()->json($candidature, 200);
    }

    /**
     * Met à jour une candidature existante.
     */
    public function update(Request $request, $id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        // Validation
        $validated = $request->validate([
            'id_formation' => 'sometimes|exists:formations,id',
            'id_candidat' => 'sometimes|exists:candidats,id',
            'statut' => 'sometimes|in:en attente,acceptée,refusée',
        ]);

        // Vérifier unicité uniquement si id_formation ou id_candidat changent
        if (isset($validated['id_formation']) || isset($validated['id_candidat'])) {
            $formationId = $validated['id_formation'] ?? $candidature->id_formation;
            $candidatId = $validated['id_candidat'] ?? $candidature->id_candidat;

            $existing = Candidature::where('id_formation', $formationId)
                ->where('id_candidat', $candidatId)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Une autre candidature existe déjà pour ce candidat et cette formation',
                    'candidature' => $existing
                ], 409);
            }
        }

        $candidature->update($validated);
        return response()->json($candidature, 200);
    }

    /**
     * Supprime une candidature.
     */
    public function destroy($id)
    {
        $candidature = Candidature::find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $candidature->delete();
        return response()->json(['message' => 'Candidature supprimée avec succès'], 200);
    }
}
