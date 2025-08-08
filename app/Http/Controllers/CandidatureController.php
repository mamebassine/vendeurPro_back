<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Formation;
use App\Models\Commission;
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


use Illuminate\Support\Facades\Auth;
use App\Notifications\StatutCandidatureModifie;
use App\Notifications\CandidatureReçueNotification;

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

    public function store(Request $request)
{
    $request->validate([
        'nom' => 'required',
        'prenom' => 'required',
        'email' => 'required|email',
        'telephone' => 'required',
        'adresse' => 'required',
        'genre' => 'required',
        'formation_id' => 'required|exists:formations,id',
        'code_parrainage' => 'nullable|string|max:12',
]);
// Création du candidat
    $candidat = Candidat::create([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'email' => $request->email,
        'telephone' => $request->telephone,
        'adresse' => $request->adresse,
        'genre' => $request->genre,
    ]);
// Création de la candidature
    $candidature = Candidature::create([
        'id_formation' => $request->formation_id,
        'id_candidat' => $candidat->id,
        'code_parrainage' => $request['code_parrainage'] ?? null,
        'statut' => 'en attente',
    ]);

    return response()->json($candidature->load('formation', 'candidat'), 201);
}
// public function store(Request $request)
// {
//     // Valider les champs du formulaire
//     $data = $request->validate([
//         'nom' => 'required',
//         'prenom' => 'required',
//         'email' => 'required|email|unique:candidats',
//         'telephone' => 'required|unique:candidats',
//         'adresse' => 'required',
//         'genre' => 'nullable|in:homme,femme',
//         'id_formation' => 'required|exists:formations,id',
//         // 'code_parrainage' => 'nullable|string|max:12',
//     ]);

//     // 1. On enregistre le candidat
//     $candidat = Candidat::create([
//         'nom' => $data['nom'],
//         'prenom' => $data['prenom'],
//         'email' => $data['email'],
//         'telephone' => $data['telephone'],
//         'adresse' => $data['adresse'],
//         'genre' => $data['genre'] ?? null,
//     ]);

//     // 2. On crée la candidature avec le code de parrainage
//     Candidature::create([
//         'id_formation' => $data['id_formation'],
//         'id_candidat' => $candidat->id,
//         'code_parrainage' => $data['code_parrainage'] ?? null,
//         'statut' => 'en attente',
//     ]);

//     return response()->json(['message' => 'Candidature enregistrée !']);
// }





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
            'code_parrainage' => 'nullable|string',
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
            'code_parrainage' => $validated['code_parrainage'] ?? null,
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
    $candidature = Candidature::with('formation', 'candidat')->find($id);

    if (!$candidature) {
        return response()->json(['message' => 'Candidature non trouvée'], 404);
    }

    $validated = $request->validate([
        'id_formation' => 'sometimes|exists:formations,id',
        'id_candidat' => 'sometimes|exists:candidats,id',
        'statut' => 'sometimes|in:en attente,acceptée,refusée',
    ]);

    $ancienStatut = $candidature->statut;

    // Vérifier doublon si formation ou candidat change
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

    // Si le statut a changé, envoyer une notification et gérer la commission
    if (isset($validated['statut']) && $validated['statut'] !== $ancienStatut) {
        $candidat = $candidature->candidat;
        if ($candidat && $candidat->email) {
            $candidat->notify(new StatutCandidatureModifie($validated['statut']));
        }

        if ($validated['statut'] === 'acceptée') {
            $existingCommission = Commission::where('candidature_id', $candidature->id)->first();
            if (!$existingCommission) {
                // Trouver le parrain via code_parrainage
                $parrain = null;
                if ($candidat->code_parrainage) {
                    $parrain = Candidat::where('code_parrainage', $candidat->code_parrainage)
                                ->where('id', '!=', $candidat->id)
                                ->first();
                }

                // Récupérer le prix de la formation
                $formation = $candidature->formation;
                $prix = $formation?->prix;
                if (is_null($prix) || $prix <= 0) {
                    Log::warning('Impossible de calculer la commission : prix invalide', [
                        'candidature_id' => $candidature->id,
                        'formation_id' => $formation?->id,
                        'prix' => $prix,
                    ]);
                } else {
                    $taux = config('commissions.taux_parrainage', 0.1);
                    $montant = round($prix * $taux, 2);

                    Log::info('Création de commission', [
                        'candidature_id' => $candidature->id,
                        'prix' => $prix,
                        'taux' => $taux,
                        'montant' => $montant,
                        'code_parrainage' => $candidat->code_parrainage,
                        'parrain_id' => $parrain?->id,
                        'valideur_id' => Auth::id(),
                    ]);

                    Commission::create([
                        'candidature_id' => $candidature->id,
                        'montant_commission' => $montant,
                        'code_parrainage' => $candidat->code_parrainage,
                        'commission_versee' => false,
                    ]);

                    if ($parrain) {
                        // Optionnel : notifier le parrain
                        // $parrain->notify(new CommissionGagneeNotification($montant, $candidat, $formation));
                    }
                }
            }
        } else {
            // Si on descend de "acceptée" vers un autre statut, supprimer la commission existante
            if ($ancienStatut === 'acceptée') {
                $commission = Commission::where('candidature_id', $candidature->id)->first();
                if ($commission) {
                    $commission->delete();
                    Log::info('Commission supprimée à cause du changement de statut', [
                        'candidature_id' => $candidature->id,
                        'nouveau_statut' => $validated['statut'],
                    ]);
                }
            }
        }
    }

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
