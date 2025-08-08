<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Commission;
use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    // Afficher toutes les commissions
    public function index()
    {
        $commissions = Commission::with(['candidature', 'valideur'])->get();
        return response()->json($commissions);
    }

    // // Enregistrer une nouvelle commission
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
           
    //         'candidature_id' => 'required|exists:candidatures,id',
    //         'montant_commission' => 'required|numeric|min:0',
    //         'code_parrainage' => 'required|string',
    //         'commission_versee' => 'boolean'
    //     ]);

    //     $commission = Commission::create($validated);
    //     return response()->json($commission, 201);
    // }

    // Afficher une commission par ID
    public function show($id)
    {
        $commission = Commission::with(['candidature', 'valideur'])->findOrFail($id);
        return response()->json($commission);
    }

    // Mettre à jour une commission
    // public function update(Request $request, $id)
    // {
    //     $commission = Commission::findOrFail($id);

    //     $validated = $request->validate([
    //         'montant_commission' => 'nullable|numeric|min:0',
    //         'commission_versee' => 'nullable|boolean',
    //         'user_id' => 'nullable|exists:users,id'
    //     ]);

    //     $commission->update($validated);
    //     return response()->json($commission);
    // }




    // Supprimer une commission
    public function destroy($id)
    {
        $commission = Commission::findOrFail($id);
        $commission->delete();

        return response()->json(['message' => 'Commission supprimée avec succès.']);
    }

   
    
}

