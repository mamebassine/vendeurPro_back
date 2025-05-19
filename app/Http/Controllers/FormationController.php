<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index()
    {
        return response()->json(Formation::with('categorie')->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'date_debut_candidature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'date_limite_depot' => 'nullable|date',
            'date_heure' => 'nullable|date_format:Y-m-d H:i:s',
            'duree' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
            'id_categorie' => 'required|exists:categories,id',
        ]);

        $formation = Formation::create($validated);
        return response()->json($formation, 201);
    }

    public function show($id)
    {
        $formation = Formation::with('categorie')->find($id);
        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }
        return response()->json($formation, 200);
    }

    public function update(Request $request, $id)
    {
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
            'date_heure' => 'nullable|date_format:Y-m-d H:i:s',
            'duree' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
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
