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
        // Pagination pour limiter la quantité de données retournées
        $candidats = Candidat::paginate(5);
        return response()->json($candidats, 200);
    }

    /**
     * Crée un nouveau candidat.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:candidats,email|max:255',
            'telephone' => 'required|string|unique:candidats,telephone|max:50',
            'adresse' => 'required|string|max:500',
            'genre' => 'nullable|in:homme,femme',
        ]);

        $candidat = Candidat::create($validated);

        return response()->json($candidat, 201);
    }

    /**
     * Affiche un candidat par son ID.
     */
    public function show($id)
    {
        $candidat = Candidat::find($id);

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        return response()->json($candidat, 200);
    }

    /**
     * Met à jour un candidat existant.
     */
    public function update(Request $request, $id)
    {
        $candidat = Candidat::find($id);

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:candidats,email,' . $id . '|max:255',
            'telephone' => 'sometimes|required|string|unique:candidats,telephone,' . $id . '|max:50',
            'adresse' => 'sometimes|required|string|max:500',
            'genre' => 'nullable|in:homme,femme',
        ]);

        $candidat->update($validated);

        return response()->json($candidat, 200);
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
