<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Candidat;
use App\Models\Commission;
use Illuminate\Support\Facades\Auth;
class CandidatureController extends Controller
{
    public function index()
    {
        return response()->json(Candidature::all(), 200);
    }

    public function stores(Request $request)
    {
        // Vérifier si une candidature existe déjà pour cette formation et ce candidat
        $existing = Candidature::where('id_formation', $request->input('id_formation'))
            ->where('id_candidat', $request->input('id_candidat'))
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Candidature déjà existante pour cette formation',
                'candidature' => $existing
            ], 200);
        }

        $candidature = Candidature::create($request->all());
        return response()->json($candidature, 201);
    }
// ca cest ma fonction store Candidature +Commission
public function store(Request $request)
{
    //  Validation des champs
    $data = $request->validate([
        'id_formation' => 'required|exists:formations,id',
        'id_candidat'  => 'required|exists:candidats,id',
        'statut'       => 'required|string',
        'pourcentage'  => 'nullable|numeric|min:0|max:100',
    ]);

    //  Empêche les doublons
    if (Candidature::where('id_formation', $data['id_formation'])
        ->where('id_candidat', $data['id_candidat'])
        ->exists()
    ) {
        return response()->json([
            'message' => 'Candidature déjà existante pour cette formation.',
        ], 200);
    }

    //  Ouvre une transaction
    DB::beginTransaction();

    try {
        // Crée la candidature et recharge la formation
        $candidature = Candidature::create([
            'id_formation' => $data['id_formation'],
            'id_candidat'  => $data['id_candidat'],
            'statut'       => $data['statut'],
        ]);
        $candidature->load('formation');

        $commission = null;

        //  Crée la commission si les conditions sont réunies
        if (
            $candidature->statut === 'acceptée'
            && $candidature->formation
            && ($filleul = $candidature->candidat)
            && $filleul->parrain_id
            && isset($data['pourcentage'])
        ) {
            $prixFormation = $candidature->formation->prix;
            $montant = round($prixFormation * $data['pourcentage'] / 100, 2);

            $commission = Commission::create([
                'parrain_id'         => $filleul->parrain_id,
                'filleul_id'         => $filleul->id,
                'candidature_id'     => $candidature->id,
                'user_id'            => Auth::id(),
                'montant_commission' => $montant,
                'date_commission'    => Carbon::now(),
            ]);
        }

        //  Engage la transaction
        DB::commit();

        // Réponse claire
        return response()->json([
            'message'     => 'Candidature créée.' . ($commission ? ' Commission générée.' : ''),
            'candidature' => $candidature,
            'commission'  => $commission,
        ], 201);

    } catch (\Exception $e) {
        //  Annule en cas d'erreur
        DB::rollBack();

        return response()->json([
            'message' => 'Erreur lors de la création.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

//ca cest la fonction  update candidature et commission
public function update(Request $request, $id)
{
    // Validation
    $data = $request->validate([
        'statut'      => 'sometimes|required|string',
        'pourcentage' => 'nullable|numeric|min:0|max:100',
    ]);

    // Récupération de la candidature avec relations nécessaires
    $candidature = Candidature::with(['formation', 'candidat'])->findOrFail($id);

    DB::beginTransaction();

    try {
        // Mise à jour du statut s'il est fourni
        if (isset($data['statut'])) {
            $candidature->statut = $data['statut'];
        }
        $candidature->save();

        $commission = null;

        // Création ou mise à jour de la commission SEULEMENT si acceptée
        if (
            $candidature->statut === 'acceptée' &&
            $candidature->formation &&
            ($filleul = $candidature->candidat) &&
            $filleul->parrain_id &&
            isset($data['pourcentage'])
        ) {
            $prixFormation = $candidature->formation->prix;
            $montant = round($prixFormation * $data['pourcentage'] / 100, 2);

            // Vérifie s'il existe déjà une commission
            $commission = Commission::where('candidature_id', $candidature->id)->first();

            if ($commission) {
                $commission->update([
                    'montant_commission' => $montant,
                    'date_commission'    => Carbon::now(),
                ]);
            } else {
                $commission = Commission::create([
                    'parrain_id'         => $filleul->parrain_id,
                    'filleul_id'         => $filleul->id,
                    'candidature_id'     => $candidature->id,
                    'user_id'            => Auth::id(),
                    'montant_commission' => $montant,
                    'date_commission'    => Carbon::now(),
                ]);
            }

            // Charger la commission dans la relation
            $candidature->load('formation');

          
        }

        DB::commit();

        return response()->json([
            'message'     => 'Candidature mise à jour.' . ($commission ? ' Commission créée ou mise à jour.' : ''),
            'candidature' => $candidature,
            'commission'  => $commission,

        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Erreur lors de la mise à jour.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


    public function show($id)
    {
        $candidature = Candidature::find($id);
        return $candidature ? response()->json($candidature, 200) : response()->json(['message' => 'Candidature non trouvée'], 404);
    }

    public function updates(Request $request, $id)
    {
        $candidature = Candidature::find($id);
        if (!$candidature) return response()->json(['message' => 'Candidature non trouvée'], 404);

        $candidature->update($request->all());
        return response()->json($candidature, 200);
    }

    public function destroy($id)
    {
        $candidature = Candidature::find($id);
        if (!$candidature) return response()->json(['message' => 'Candidature non trouvée'], 404);

        $candidature->delete();
        return response()->json(['message' => 'Candidature supprimée'], 200);
    }
}
