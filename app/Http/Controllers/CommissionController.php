<?php

namespace App\Http\Controllers;
use App\Models\Commission;
use App\Models\Candidature;
use App\Models\Formation;
use App\Models\Candidat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;


class CommissionController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'candidature_id' => 'required|exists:candidatures,id',
        'pourcentage' => 'required|numeric|min:0|max:100',
    ]);

    // Récupérer la candidature avec les relations nécessaires
    $candidature = Candidature::with('formation')->findOrFail($request->candidature_id);

    // Vérifier si la candidature est acceptée
    if ($candidature->statut !== 'acceptée') {
        return response()->json([
            'message' => 'La commission ne peut être créée que pour les candidatures acceptées.',
        ], 400);
    }

    // Vérifier si une commission existe déjà pour cette candidature
    $existe = Commission::where('candidature_id', $candidature->id)->exists();
    if ($existe) {
        return response()->json([
            'message' => 'Une commission a déjà été créée pour cette candidature.',
        ], 409);
    }

    // Récupérer le filleul (le candidat concerné)
    $filleul = Candidat::findOrFail($candidature->id_candidat);

    // Vérifier s'il a un parrain
    if (!$filleul->parrain_id) {
        return response()->json([
            'message' => 'Ce candidat n\'a pas de parrain, impossible de générer une commission.',
        ], 400);
    }

    // Récupérer le prix de la formation
    $prixFormation = $candidature->formation->prix ?? 0;

    // Calcul de la commission
    $montant_commission = round(($prixFormation * $request->pourcentage) / 100, 2);

    // Création de la commission
    $commission = Commission::create([
        'parrain_id' => $filleul->parrain_id,
        'filleul_id' => $filleul->id,
        'candidature_id' => $candidature->id,
        'user_id' => Auth::id(),
        'montant_commission' => $montant_commission,
        'date_commission' => Carbon::now(),
    ]);

    return response()->json([
        'message' => 'Commission créée avec succès.',
        'commission' => $commission,
    ], 201);
}


public function update(Request $request, $id)
{
    $request->validate([
        'pourcentage'    => 'required|numeric|min:0|max:100',
        'candidature_id' => 'required|exists:candidatures,id',
    ]);

    $commission = Commission::findOrFail($id);

    // Met à jour la candidature associée
    $commission->candidature_id = $request->candidature_id;
    $commission->save();

    // Recharger la candidature
    $candidature = Candidature::with('formation')
        ->findOrFail($commission->candidature_id);

    if ($candidature->statut !== 'acceptée') {
        return response()->json([
            'message' => 'La commission ne peut être modifiée que pour les candidatures acceptées.',
        ], 400);
    }

    $filleul = Candidat::findOrFail($candidature->id_candidat);
    if (!$filleul->parrain_id) {
        return response()->json([
            'message' => 'Ce candidat n\'a pas de parrain, impossible de modifier la commission.',
        ], 400);
    }

    $prixFormation = $candidature->formation->prix ?? 0;
    $montant_commission = round(($prixFormation * $request->pourcentage) / 100, 2);

    $commission->update([
        'montant_commission' => $montant_commission,
        'date_commission'    => now(),
        'user_id'            => Auth::id(),
    ]);

    return response()->json([
        'message'   => 'Commission modifiée avec succès.',
        'commission'=> $commission,
    ], 200);
}

public function shows($id)
{
    try {
        // Récupération de la commission avec toutes les relations utiles sans restreindre les colonnes
        $commission = Commission::with([
            'parrain',
            'filleul',
            'candidature',
            'candidature.candidat',
            'candidature.formation',
            'user'
        ])->findOrFail($id);

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Détails de la commission récupérés avec succès',
            'data' => $commission,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la commission',
            'error' => $e->getMessage(),
        ]);
    }
}

public function show($id)
{
    $userId = Auth::id();

    try {
        $commission = Commission::with([
            'parrain',
            'filleul',
            'candidature',
            'candidature.candidat',
            'candidature.formation',
            'user',
        ])->findOrFail($id);

        // 🛡️ Autorisation : uniquement le créateur
        if ($commission->user_id !== $userId) {
            return response()->json([
                'status_code'    => 403,
                'status_message' => 'Accès refusé : vous n\'êtes pas le créateur de cette commission.',
            ], 403);
        }

        return response()->json([
            'status_code'    => 200,
            'status_message' => 'Détails de la commission récupérés avec succès',
            'data'           => $commission,
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status_code'    => 404,
            'status_message' => 'Commission non trouvée.',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status_code'    => 500,
            'status_message' => 'Une erreur s\'est produite lors de la récupération de la commission',
            'error'          => $e->getMessage(),
        ], 500);
    }
}


// Récupérer toutes les commissions avec leurs relations
public function index()
{
    $userId = Auth::id();

    try {
        $commissions = Commission::with([
            'parrain',
            'filleul',
            'candidature',
            'candidature.candidat',
            'candidature.formation',
            'user',
        ])
        ->where('user_id', $userId) // seul le créateur concerné
        ->get();

        if ($commissions->isEmpty()) {
            return response()->json([
                'status_code'    => 403,
                'status_message' => 'Accès refusé : vous n\'êtes pas le créateur d’aucune commission.',
            ], 403);
        }

        return response()->json([
            'status_code'    => 200,
            'status_message' => 'Liste de vos commissions récupérée avec succès',
            'data'           => $commissions,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status_code'    => 500,
            'status_message' => 'Une erreur est survenue lors de la récupération des commissions.',
            'error'          => $e->getMessage(),
        ], 500);
    }
}

//afficher toutes les commissions d'un parrain et lui faire la somme totale
public function indexParrainCommissions($parrainId)
{
    $parrain = Candidat::withSum('commissionsRecues', 'montant_commission')
        ->with([
            'commissionsRecues' => function ($query) {
                $query->with([
                    'filleul:id,nom,prenom,email,telephone,adresse,parrain_id',
                    'candidature' => function ($q) {
                        $q->select('id', 'id_candidat', 'id_formation', 'statut')
                          ->with([
                              'candidat:id,nom,prenom,email,telephone',
                              'formation:id,titre,description,date_debut,date_fin,prix,lieu,type,id_categorie'
                          ]);
                    }
                ]);
            }
        ])
        ->findOrFail($parrainId);

    return response()->json([
        'parrain_id'        => $parrain->id,
        'nom'               => $parrain->nom,
        'prenom'            => $parrain->prenom,
        'email'             => $parrain->email,
        'telephone'         => $parrain->telephone,
        'total_commissions' => round($parrain->commissions_recues_sum_montant_commission, 2),
        'commissions'       => $parrain->commissionsRecues->map(function ($commission) {
            return [
                'id'                 => $commission->id,
                'montant_commission' => $commission->montant_commission,
                'date_commission'    => $commission->date_commission,

                'filleul' => [
                    'id'        => $commission->filleul->id,
                    'nom'       => $commission->filleul->nom,
                    'prenom'    => $commission->filleul->prenom,
                    'email'     => $commission->filleul->email,
                    'telephone' => $commission->filleul->telephone,
                    'adresse'   => $commission->filleul->adresse,
                    'parrain_id'=> $commission->filleul->parrain_id,
                ],

                'candidature' => [
                    'id'          => $commission->candidature->id,
                    'id_candidat' => $commission->candidature->id_candidat,
                    'id_formation'=> $commission->candidature->id_formation,
                    'statut'      => $commission->candidature->statut,

                    'candidat' => [
                        'id'        => $commission->candidature->candidat->id,
                        'nom'       => $commission->candidature->candidat->nom,
                        'prenom'    => $commission->candidature->candidat->prenom,
                        'email'     => $commission->candidature->candidat->email,
                        'telephone' => $commission->candidature->candidat->telephone,
                    ],

                    'formation' => [
                        'id'           => $commission->candidature->formation->id,
                        'titre'        => $commission->candidature->formation->titre,
                        'description'  => $commission->candidature->formation->description,
                        'date_debut'   => $commission->candidature->formation->date_debut->format('Y-m-d'),
                        'date_fin'     => $commission->candidature->formation->date_fin->format('Y-m-d'),
                        'prix'         => $commission->candidature->formation->prix,
                        'lieu'         => $commission->candidature->formation->lieu,
                        'type'         => $commission->candidature->formation->type,
                        'id_categorie' => $commission->candidature->formation->id_categorie,
                    ],
                ],
            ];
        }),
    ], 200);
}

public function destroy($id)
{
    try {
        // Vérifier l'existence de la commission
        $commission = Commission::findOrFail($id);

        // Supprimer la commission
        $commission->delete();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Commission supprimée avec succès.',
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status_code' => 500,
            'status_message' => 'Une erreur s\'est produite lors de la suppression de la commission.',
            'error' => $e->getMessage(),
        ]);
    }
}
}
