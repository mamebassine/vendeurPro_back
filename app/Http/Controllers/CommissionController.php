<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    /**
     * Création d'une commission
     */
    public function createCommission($candidature, $parrain_id = null, $montant = 0)
    {
        $commission = Commission::create([
            'candidature_id'   => $candidature->id,
            'user_id'          => $parrain_id, // Le vrai parrain
            'montant_commission' => $montant,
            'code_parrainage'  => $candidature->code_parrainage,
            'commission_versee'=> 0,
            'valideur_id'      => null, // rempli uniquement lors de la validation
        ]);

        return $commission;
    }

    /**
     * Liste de toutes les commissions avec infos parrain, candidat et formation (admin)
     */
    public function listeCommissions()
    {
        $commissions = Commission::with([
            'candidature.candidat',
            'candidature.formation', // formation liée
            'candidature.user',      // le parrain lié à la candidature
            'valideur'
        ])->get();

        return response()->json([
            'success'     => true,
            'commissions' => $commissions
        ]);
    }

    /**
     * Liste des commissions liées à un utilisateur (parrain)
     */
    public function mesCommissions()
    {
        $user = Auth::user();

        $commissions = Commission::with([
            'candidature.candidat',
            'candidature.formation', // formation liée
            'candidature.user',      // parrain lié
            'valideur'
        ])
            ->whereHas('candidature', function ($query) use ($user) {
                $query->where('code_parrainage', $user->code_parrainage);
            })
            ->get();

        return response()->json([
            'success'     => true,
            'commissions' => $commissions
        ]);
    }

 /**
     * Afficher une commission par ID
     */
    public function show($id)
    {
        $commission = Commission::with([
            'candidature.candidat',
            'candidature.formation',
            'candidature.user',  // parrain
            'valideur'
        ])->findOrFail($id);

        return response()->json([
            'success'    => true,
            'commission' => $commission
        ]);
    }

    /**
     * Supprimer une commission
     */
    public function destroy($id)
    {
        $commission = Commission::findOrFail($id);
        $commission->delete();

        return response()->json(['message' => 'Commission supprimée avec succès.']);
    }

    /**
     * Valider une commission
     */
    public function validerCommission($id)
    {
        $commission = Commission::findOrFail($id);

        // Récupérer la candidature associée
        $candidature = $commission->candidature;
        if (!$candidature) {
            return response()->json([
                'success' => false,
                'message' => 'Candidature non trouvée pour cette commission.'
            ], 404);
        }

        // Récupérer le parrain via la relation user() dans Candidature
        $parrain = $candidature->user;
        if (!$parrain) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun parrain trouvé pour ce candidat.'
            ], 404);
        }

        // Calculer le montant de la commission (exemple : 10% du prix de la formation)
        $montant = $candidature->formation->prix * 0.10;

        // Mettre à jour la commission
        $commission->commission_versee   = 1;
        $commission->montant_commission  = $montant;
        $commission->valideur_id         = Auth::id(); // l'admin qui valide
        $commission->save();

        // Verser la commission au parrain
        $parrain->solde += $montant;
        $parrain->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Commission validée et versée au parrain.',
            'commission' => $commission->load(['candidature.formation', 'candidature.candidat', 'candidature.user']),
            'parrain'    => $parrain
        ]);
    }


    /**
 * Montant total des commissions pour le parrain connecté
 */
// public function montantTotalMesCommissions()
// {
//     $user = Auth::user();

//     // Somme des commissions liées à ce parrain
//     $total = Commission::whereHas('candidature', function ($query) use ($user) {
//         $query->where('code_parrainage', $user->code_parrainage);
//     })->sum('montant_commission');

//     return response()->json([
//         'success' => true,
//         'montant_total' => $total
//     ]);
// }

public function montantTotalMesCommissions()
{
    $user = Auth::user();

    // Somme des commissions liées à ce parrain
    $total = Commission::whereHas('candidature', function ($query) use ($user) {
        $query->where('code_parrainage', $user->code_parrainage);
    })->sum('montant_commission');

    return response()->json([
        'success' => true,
        'montant_total' => $total
    ]);
}

}
