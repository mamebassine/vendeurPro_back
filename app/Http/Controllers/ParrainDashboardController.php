<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Candidature;
use App\Models\Commission;

class ParrainDashboardController extends Controller
{
    public function index()
    {
        $parrain = Auth::user();

        // Toutes les candidatures du parrain
        $candidaturesParrain = Candidature::with(['candidat'])
            ->where('code_parrainage', $parrain->code_parrainage)
            ->get();

        // Nombre de filleuls distincts
        $filleulsCount = $candidaturesParrain->pluck('candidat')->unique('id')->count();

        // Candidatures en attente
        $candidaturesEnAttente = $candidaturesParrain->where('statut', 'en attente')->count();

        // Commissions totales directement depuis la table commissions
        $commissionsTotales = Commission::whereIn('candidature_id', $candidaturesParrain->pluck('id'))
            ->sum('montant_commission');

        // Solde = commissions non versées
        $solde = Commission::whereIn('candidature_id', $candidaturesParrain->pluck('id'))
            ->where('commission_versee', false)
            ->sum('montant_commission');

        return response()->json([
            'success' => true,
            'parrain' => [
                'id' => $parrain->id,
                'name' => $parrain->name,
                'prenom' => $parrain->prenom,
                'code_parrainage' => $parrain->code_parrainage,
            ],
            'stats' => [
                'filleulsCount' => $filleulsCount,
                'candidaturesEnAttente' => $candidaturesEnAttente,
                'commissionsTotales' => $commissionsTotales,
                'solde' => $solde,
            ],
            'candidatures' => $candidaturesParrain
        ]);
    }
}
