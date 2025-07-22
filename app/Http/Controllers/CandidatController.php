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
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'nom' => 'required|string|max:255',
    //         'prenom' => 'required|string|max:255',
    //         'email' => 'required|email|unique:candidats,email|max:255',
    //         'telephone' => 'required|string|unique:candidats,telephone|max:50',
    //         'adresse' => 'required|string|max:500',
    //         'genre' => 'nullable|in:homme,femme',
    //     ]);

    //     $candidat = Candidat::create($validated);

    //     return response()->json($candidat, 201);
    // }


    public function store(Request $request)
{
    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telephone' => 'required|string|max:50',
        'adresse' => 'required|string|max:500',
        'genre' => 'nullable|in:homme,femme',
        'formation_id' => 'nullable|exists:formations,id'
    ]);

    // Vérifie si un candidat avec même email ou téléphone existe
    $candidat = Candidat::where('email', $validated['email'])
                ->orWhere('telephone', $validated['telephone'])
                ->first();

    if ($candidat) {
        // Si une formation est passée, on lie ce candidat à la formation (si ce n’est pas déjà fait)
        if (isset($validated['formation_id'])) {
            $candidat->formations()->syncWithoutDetaching([$validated['formation_id']]);
        }

        return response()->json([
            'message' => 'Candidat existant relié à la formation.',
            'candidat' => $candidat
        ], 200);
    }

    // Sinon, on le crée normalement
    $nouveauCandidat = Candidat::create($validated);

    // Et on le relie à la formation s’il y en a une
    if (isset($validated['formation_id'])) {
        $nouveauCandidat->formations()->attach($validated['formation_id']);
    }

    return response()->json([
        'message' => 'Nouveau candidat créé.',
        'candidat' => $nouveauCandidat
    ], 201);
}


    /**
     * Affiche un candidat par son ID.
     */
    // public function show($id)
    // {
    //     $candidat = Candidat::find($id);

    //     if (!$candidat) {
    //         return response()->json(['message' => 'Candidat non trouvé'], 404);
    //     }

    //     return response()->json($candidat, 200);
    // }
public function show($id)
{
    $candidat = Candidat::with('formations')->find($id);

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
