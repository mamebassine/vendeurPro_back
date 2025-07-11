<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    // Liste toutes les actualités
    public function index()
    {
        return response()->json(Actualite::all(), 200);
    }

    // Crée une nouvelle actualité
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'auteur' => 'required|string|max:255',
            'fonction' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'date_publication' => 'required|date',
            'points' => 'nullable|array',
            'conclusion' => 'nullable|string',
        ]);

        // Convertir points en JSON si présent
        if (isset($validated['points'])) {
            $validated['points'] = json_encode($validated['points']);
        }

        $actualite = Actualite::create($validated);

        return response()->json($actualite, 201);
    }

    // Affiche une actualité spécifique
    public function show($id)
    {
        $actualite = Actualite::find($id);

        if (!$actualite) {
            return response()->json(['message' => 'Actualité non trouvée'], 404);
        }

        return response()->json($actualite);
    }

    // Met à jour une actualité
    public function update(Request $request, $id)
    {
        $actualite = Actualite::find($id);

        if (!$actualite) {
            return response()->json(['message' => 'Actualité non trouvée'], 404);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'contenu' => 'sometimes|required|string',
            'auteur' => 'sometimes|required|string|max:255',
            'fonction' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'date_publication' => 'nullable|date',
            'points' => 'nullable|array',
            'conclusion' => 'nullable|string',
        ]);

        // Convertir points en JSON si présent
        if (isset($validated['points'])) {
            $validated['points'] = json_encode($validated['points']);
        }

        $actualite->update($validated);

        return response()->json($actualite);
    }

    // Supprime une actualité
    public function destroy($id)
    {
        $actualite = Actualite::find($id);

        if (!$actualite) {
            return response()->json(['message' => 'Actualité non trouvée'], 404);
        }

        $actualite->delete();

        return response()->json(['message' => 'Actualité supprimée avec succès']);
    }
}
