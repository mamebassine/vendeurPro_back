<?php

namespace App\Http\Controllers;

use id;
use App\Models\Candidat;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User;

class CandidatController extends Controller
{
    /**
     * Affiche la liste paginée des candidats.
     */
 public function index()
{
    $candidats = Candidat::orderBy('created_at', 'desc')->paginate(5);
    return response()->json($candidats, 200);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telephone' => 'required|string|max:50',
        'adresse' => 'required|string|max:500',
        'genre' => 'nullable|in:homme,femme',
        'code_parrainage' => 'nullable|string',
        'formation_id' => 'nullable|exists:formations,id'
    ]);

  // Recherche d'un parrain par code de parrainage
        $parrain = null;
        if (!empty($validated['code_parrainage'])) {
            $parrain = User::where('code_parrainage', $validated['code_parrainage'])->first();
        }

        // Recherche si candidat existe déjà


    // Vérifie si un candidat avec même email ou téléphone existe
    $candidat = Candidat::where('email', $validated['email'])
                ->orWhere('telephone', $validated['telephone'])
                ->first();

if ($candidat) {
    // ✅ Mise à jour du code parrainage si le champ est vide
    if (empty($candidat->code_parrainage) && !empty($validated['code_parrainage'])) {
        $candidat->code_parrainage = $validated['code_parrainage'];
        $candidat->parrain_id = $parrain ? $parrain->id : null;
        $candidat->save();

        // Enregistre la commission si nouveau parrain
        if ($parrain) {
            Commission::create([
               'candidature_id' => null,
                'user_id' => null,
                'montant_commission' => 0,
                'code_parrainage' => $validated['code_parrainage'],
                'commission_versee' => false,
            ]);
        }
    }

    // Si une formation est passée, on lie ce candidat à la formation (si ce n’est pas déjà fait)
    if (isset($validated['formation_id'])) {
        $candidat->formations()->syncWithoutDetaching([$validated['formation_id']]);
    }

    return response()->json([
        'message' => 'Candidat existant relié à la formation.',
        'candidat' => $candidat
    ], 200);
}

 // Création du nouveau candidat
        $nouveauCandidat = Candidat::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'adresse' => $validated['adresse'],
            'genre' => $validated['genre'] ?? null,
            'code_parrainage' => $validated['code_parrainage'] ?? null,
        ]);

        // Lier à la formation s’il y en a une
        if (isset($validated['formation_id'])) {
            $nouveauCandidat->formations()->attach($validated['formation_id']);
        }

        // Création de la commission si parrain trouvé
        if ($parrain) {
            Commission::create([
                'candidature_id' => null, // tu pourras la mettre à jour plus tard
                'montant_commission' => 0, // ou un montant par défaut
                'code_parrainage' => $validated['code_parrainage'],
                'commission_versee' => false,
            ]);
        }

        return response()->json([
            'message' => 'Nouveau candidat créé.',
            'candidat' => $nouveauCandidat
        ], 201);
    }

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
