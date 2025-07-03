<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Candidat;
use Illuminate\Http\Request;
use App\Notifications\CandidatureReçueNotification;
use App\Notifications\StatutCandidatureModifie;

use Illuminate\Support\Facades\Auth;

class CandidatureController extends Controller
{
    /**
     * Liste toutes les candidatures (avec les relations si besoin).
     */
    public function index()
    {
        $candidatures = Candidature::with(['formation', 'candidat'])->paginate(15);
        return response()->json($candidatures, 200);
    }

    /**
     * Enregistre une nouvelle candidature, après vérification (via admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_formation' => 'required|exists:formations,id',
            'id_candidat' => 'required|exists:candidats,id',
            'statut' => 'required|in:en attente,acceptée,refusée',
        ]);

        $existing = Candidature::where('id_formation', $validated['id_formation'])
            ->where('id_candidat', $validated['id_candidat'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Candidature déjà existante pour cette formation',
                'candidature' => $existing
            ], 200);
        }

        $candidature = Candidature::create($validated);
        return response()->json($candidature, 201);
    }

    /**
     * Enregistrement depuis le formulaire public (sans authentification).
     */
    public function storeFromPublic(Request $request)
    {
        $validated = $request->validate([
            'formation_id' => 'required|exists:formations,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:50',
            'adresse' => 'required|string|max:500',
            'genre' => 'nullable|in:homme,femme',
        ]);

        $candidat = Candidat::where('email', $validated['email'])->first();

        if (!$candidat) {
            $candidat = Candidat::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'],
                'adresse' => $validated['adresse'],
                'genre' => $validated['genre'] ?? null,
            ]);
        }

        $existing = Candidature::where('id_formation', $validated['formation_id'])
            ->where('id_candidat', $candidat->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à cette formation.',
                'candidature' => $existing
            ], 409);
        }

        $candidature = Candidature::create([
            'id_formation' => $validated['formation_id'],
            'id_candidat' => $candidat->id,
            'statut' => 'en attente',
        ]);

        // Charger la relation formation (nécessaire pour le titre)
        $candidature->load('formation');

        // ✅ Envoi de l'e-mail de notification
        $candidat->notify(new CandidatureReçueNotification($candidature->formation->titre));

        return response()->json([
            'message' => 'Votre inscription a bien été enregistrée.',
            'candidature' => $candidature
        ], 201);
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

    $validated = $request->validate([
        'id_formation' => 'sometimes|exists:formations,id',
        'id_candidat' => 'sometimes|exists:candidats,id',
        'statut' => 'sometimes|in:en attente,acceptée,refusée',
    ]);

    $ancienStatut = $candidature->statut;

    // Vérifier doublon
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

    // Si le statut a changé, envoyer un mail
    if (isset($validated['statut']) && $validated['statut'] !== $ancienStatut) {
        $candidat = $candidature->candidat; // relation "candidat" (avec with si besoin)
        if ($candidat && $candidat->email) {
            $candidat->notify(new StatutCandidatureModifie($validated['statut']));
        }
    }

    return response()->json($candidature, 200);
}


// ANCIEN MODIFICATION (COMMENTEE CI-)

    // public function update(Request $request, $id)
    // {
    //     $candidature = Candidature::find($id);

    //     if (!$candidature) {
    //         return response()->json(['message' => 'Candidature non trouvée'], 404);
    //     }

    //     $validated = $request->validate([
    //         'id_formation' => 'sometimes|exists:formations,id',
    //         'id_candidat' => 'sometimes|exists:candidats,id',
    //         'statut' => 'sometimes|in:en attente,acceptée,refusée',
    //     ]);

    //     if (isset($validated['id_formation']) || isset($validated['id_candidat'])) {
    //         $formationId = $validated['id_formation'] ?? $candidature->id_formation;
    //         $candidatId = $validated['id_candidat'] ?? $candidature->id_candidat;

    //         $existing = Candidature::where('id_formation', $formationId)
    //             ->where('id_candidat', $candidatId)
    //             ->where('id', '!=', $id)
    //             ->first();

    //         if ($existing) {
    //             return response()->json([
    //                 'message' => 'Une autre candidature existe déjà pour ce candidat et cette formation',
    //                 'candidature' => $existing
    //             ], 409);
    //         }
    //     }

    //     $candidature->update($validated);
    //     return response()->json($candidature, 200);
    // }

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
