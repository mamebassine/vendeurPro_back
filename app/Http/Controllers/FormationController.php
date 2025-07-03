<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index()
    {
        $formations = Formation::with([
            'categorie:id,nom',
            'user:id,name,prenom'  
        ])->get();

        return response()->json($formations, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string',
            'description' => 'required|string',
            'public_vise' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'format' => 'nullable|string',
            'certifiante' => 'nullable|boolean',

            'date_debut_candidature' => 'required|date',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'date_limite_depot' => 'required|date',
            'heure' => 'required',
            'duree' => 'required|numeric',
            'prix' => 'required|numeric',
            'lieu' => 'required|string',
            'type' => 'required|string|in:Bootcamps,Formations certifiantes,Modules à la carte',
            'id_categorie' => 'required|integer|exists:categories,id',
        ]);

        $formation = new Formation($validated);
        $formation->user_id = Auth::id();
        $formation->save();

        return response()->json($formation, 201);
    }

    public function show($id)
    {
        $formation = Formation::with(['categorie:id,nom', 'user:id,name,prenom'])->find($id);

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
            'public_vise' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'format' => 'nullable|string',
            'certifiante' => 'nullable|boolean',

            'date_debut_candidature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'date_limite_depot' => 'nullable|date|after_or_equal:date_debut_candidature',
            'heure' => 'nullable|date_format:H:i',
            'duree' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
            'type' => 'nullable|in:Bootcamps,Formations certifiantes,Modules à la carte',
            'id_categorie' => 'sometimes|required|exists:categories,id',
        ]);

        $formation->update($validated);
        $formation->user_id = Auth::id();
        $formation->save();

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
