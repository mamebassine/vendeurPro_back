<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use Illuminate\Http\Request;

class CandidatController extends Controller
{
    /**
     * Affiche la liste paginée des candidats.
     */
    public function index()
    {
        $candidats = Candidat::paginate(5);
        return response()->json($candidats, 200);
    }

    /**
     * Crée un nouveau candidat avec logique de parrainage et relation formation.
     */
    public function store(Request $request)
    {
        // Validation des champs
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:candidats,email',
            'telephone' => 'required|string|max:50|unique:candidats,telephone',
            'adresse' => 'required|string|max:500',
            'genre' => 'nullable|in:homme,femme',
            'formation_id' => 'nullable|exists:formations,id',
            'code_parrain' => 'nullable|string|size:6|exists:candidats,code_parrainage', // parrainage
        ]);

        // Création du candidat
        $candidat = new Candidat($validated);

        // Gestion du parrainage
        if ($request->filled('code_parrain')) {
            $parrain = Candidat::where('code_parrainage', $request->code_parrain)->first();
            if ($parrain) {
                $candidat->parrain_id = $parrain->id;
            }
        }

        $candidat->save();

        // Lier une formation si présente
        if (isset($validated['formation_id'])) {
            $candidat->formations()->attach($validated['formation_id']);
        }

        return response()->json([
            'message' => 'Candidat enregistré avec succès.',
            'candidat' => $candidat
        ], 201);
    }

    /**
     * Affiche un candidat par son ID (avec ses formations).
     */
    public function show($id)
    {
        $candidat = Candidat::with('formations')->find($id);
        return $candidat 
            ? response()->json($candidat, 200)
            : response()->json(['message' => 'Candidat non trouvé'], 404);
    }

    /**
     * Met à jour un candidat existant (avec parrainage).
     */
    public function update(Request $request, $id)
    {
        $candidat = Candidat::find($id);

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        // Validation des champs
        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:candidats,email,' . $id,
            'telephone' => 'sometimes|required|string|max:50|unique:candidats,telephone,' . $id,
            'adresse' => 'sometimes|required|string|max:500',
            'genre' => 'nullable|in:homme,femme',
            'code_parrain' => 'nullable|string|size:6|exists:candidats,code_parrainage',
        ]);

        $candidat->update($validated);

        // Mise à jour du parrainage
        if ($request->filled('code_parrain')) {
            $parrain = Candidat::where('code_parrainage', $request->code_parrain)->first();
            if ($parrain) {
                $candidat->parrain_id = $parrain->id;
                $candidat->save();
            }
        }

        return response()->json([
            'message' => 'Candidat mis à jour avec succès.',
            'candidat' => $candidat
        ], 200);
    }

    /**
     * Supprime un candidat par son ID.
     */
    public function destroy($id)
    {
        $candidat = Candidat::find($id);

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        $candidat->delete();

        return response()->json(['message' => 'Candidat supprimé'], 200);
    }
}
