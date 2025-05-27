<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index()
    {
        $formations = Formation::with(['categorie:id,nom'])->get();

        // Pas besoin d'ajouter ici, l'accessor dans le modèle gère le formatage

        return response()->json($formations, 200);
    }

    public function store(Request $request)
    {
        Log::info('Données reçues dans store:', $request->all());

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'date_debut_candidature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'date_limite_depot' => 'nullable|date|after_or_equal:date_debut_candidature',

'heure' => 'nullable|date_format:H:i',

            'duree' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
            'type' => 'nullable|in:Bootcamps,Formations certifiantes,Modules à la carte',
            'id_categorie' => 'required|exists:categories,id',
        ]);

        $formation = Formation::create($validated);
        return response()->json($formation, 201);
    }

    public function show($id)
    {
        $formation = Formation::with(['categorie:id,nom'])->find($id);
        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }
        return response()->json($formation, 200);
    }

    public function update(Request $request, $id)
    {
        Log::info("Données reçues dans update pour l'ID $id :", $request->all());

        $formation = Formation::find($id);
        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'date_debut_candidature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'date_limite_depot' => 'nullable|date',
    'heure' => 'nullable|date_format:H:i',
            'duree' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
            'type' => 'nullable|in:Bootcamps,Formations certifiantes,Modules à la carte',
            'id_categorie' => 'sometimes|required|exists:categories,id',
        ]);

        $formation->update($validated);
        return response()->json($formation, 200);
    }

    public function destroy($id)
    {
        $formation = Formation::find($id);
        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }

        $formation->delete();
        return response()->json(['message' => 'Formation supprimée'], 200);
    }
}
