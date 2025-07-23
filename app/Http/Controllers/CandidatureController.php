<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use App\Models\Candidat;
use App\Models\Commission;
use Illuminate\Http\Request;
use App\Notifications\CandidatureReçueNotification;
use App\Notifications\StatutCandidatureModifie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
     * Crée une nouvelle candidature + gestion de commission (via admin).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_formation' => 'required|exists:formations,id',
            'id_candidat'  => 'required|exists:candidats,id',
            'statut'       => 'required|in:en attente,acceptée,refusée',
            'pourcentage'  => 'nullable|numeric|min:0|max:100',
        ]);

        // Empêche les doublons
        if (Candidature::where('id_formation', $data['id_formation'])
            ->where('id_candidat', $data['id_candidat'])
            ->exists()
        ) {
            return response()->json([
                'message' => 'Candidature déjà existante pour cette formation.',
            ], 200);
        }

        DB::beginTransaction();

        try {
            // Crée la candidature
            $candidature = Candidature::create([
                'id_formation' => $data['id_formation'],
                'id_candidat'  => $data['id_candidat'],
                'statut'       => $data['statut'],
            ]);
            $candidature->load('formation', 'candidat');

            $commission = null;

            // Crée la commission si acceptée et parrainage présent
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

            DB::commit();

            return response()->json([
                'message'     => 'Candidature créée.' . ($commission ? ' Commission générée.' : ''),
                'candidature' => $candidature,
                'commission'  => $commission,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erreur lors de la création.',
                'error'   => $e->getMessage(),
            ], 500);
        }
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

        $candidature->load('formation');
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
     * Met à jour une candidature + commission.
     */
    public function update(Request $request, $id)
    {
        $candidature = Candidature::with(['formation', 'candidat'])->find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        $data = $request->validate([
            'statut'      => 'sometimes|required|string|in:en attente,acceptée,refusée',
            'pourcentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $ancienStatut = $candidature->statut;

        DB::beginTransaction();

        try {
            if (isset($data['statut'])) {
                $candidature->statut = $data['statut'];
            }
            $candidature->save();

            $commission = null;

            if (
                $candidature->statut === 'acceptée'
                && $candidature->formation
                && ($filleul = $candidature->candidat)
                && $filleul->parrain_id
                && isset($data['pourcentage'])
            ) {
                $prixFormation = $candidature->formation->prix;
                $montant = round($prixFormation * $data['pourcentage'] / 100, 2);

                $commission = Commission::updateOrCreate(
                    ['candidature_id' => $candidature->id],
                    [
                        'parrain_id'         => $filleul->parrain_id,
                        'filleul_id'         => $filleul->id,
                        'user_id'            => Auth::id(),
                        'montant_commission' => $montant,
                        'date_commission'    => Carbon::now(),
                    ]
                );
            }

            DB::commit();

            // Notification de changement de statut
            if (isset($data['statut']) && $data['statut'] !== $ancienStatut) {
                $candidat = $candidature->candidat;
                if ($candidat) {
                    $candidat->notify(new StatutCandidatureModifie($data['statut']));
                }
            }

            return response()->json([
                'message'     => 'Candidature mise à jour.' . ($commission ? ' Commission mise à jour.' : ''),
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
