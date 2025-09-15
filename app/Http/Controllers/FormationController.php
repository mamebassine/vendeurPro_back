<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FormationController extends Controller
{
    /**
     * Lister toutes les formations, coachings, webinaires, etc.
     */
public function index(Request $request)
{
    // 🔹 Récupère le code de parrainage si présent dans l'URL
    $codeParrainage = $request->query('ref');

    // 🔹 Récupère toutes les formations avec leur catégorie et auteur
    $formations = Formation::with([
        'categorie:id,nom',
        'user:id,name,prenom'
    ])->get();

    // 🔹 Retour JSON avec code de parrainage
    return response()->json([
        'success' => true,
        'formations' => $formations,
        'code_parrainage' => $codeParrainage
    ], 200);
}



//     public function index()
//     {
//         $formations = Formation::with([
//             'categorie:id,nom',
//             'user:id,name,prenom'
//         ])->get();

// return response()->json($formations, 200);
//     }

    /**
     * Afficher uniquement les formations dont la catégorie est "formation"
     */
    public function afficherFormations()
    {
        $categorie = Categorie::where('nom', 'formation')->first();

        if (!$categorie) {
            return response()->json([], 200);
        }

        $formations = Formation::with('categorie')
            ->where('id_categorie', $categorie->id)
            ->get();

        return response()->json($formations, 200);
    }

    /**
     * Afficher uniquement les coachings
     */
    public function afficherCoaching()
    {
        $categorie = Categorie::where('nom', 'coaching')->first();

        if (!$categorie) {
            return response()->json([], 200);
        }

        $formations = Formation::with('categorie')
            ->where('id_categorie', $categorie->id)
            ->get();

        return response()->json($formations, 200);
    }

    /**
     * Afficher uniquement les webinaires
     */
    public function afficherWebinaire()
    {
        $categorie = Categorie::where('nom', 'webinaire')->first();

        if (!$categorie) {
            return response()->json([], 200);
        }

        $formations = Formation::with('categorie')
            ->where('id_categorie', $categorie->id)
            ->get();

        return response()->json($formations, 200);
    }

    /**
     * Ajouter les formations, coaching ou webinaire
     */
    public function ajouterWebinaire(Request $request)
    {
        $categorie = Categorie::where('nom', 'webinaire')->first();

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie webinaire non trouvée'], 404);
        }

        // On ajoute l'id de la catégorie webinaire dans la requête pour l'enregistrer
        $request->merge(['id_categorie' => $categorie->id]);

        return $this->store($request);
    }

    /**
     * Ajouter un nouveau coaching
     */
    public function ajouterCoaching(Request $request)
    {
        $categorie = Categorie::where('nom', 'coaching')->first();

        if (!$categorie) {
            return response()->json(['message' => 'Catégorie coaching non trouvée'], 404);
        }

        // On ajoute l'id de la catégorie coaching dans la requête pour l'enregistrer
        $request->merge(['id_categorie' => $categorie->id]);

        return $this->store($request);
    }

    /**
     * Enregistrer une nouvelle formation, coaching ou webinaire
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',

            // Champs spécifiques à une formation (optionnels pour coaching/webinaire)
            'description' => 'nullable|string',
            'public_vise' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'format' => 'nullable|string',
            'certifiante' => 'nullable|boolean',
            'date_debut_candidature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'date_limite_depot' => 'nullable|date',
            'type' => 'nullable|in:Bootcamps,Formations certifiantes,Modules à la carte',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
            'heure' => 'nullable|date_format:H:i',

            // Champs communs obligatoires
            'duree' => 'required|numeric|min:1',
            'id_categorie' => 'required|exists:categories,id',
        ]);

        $formation = new Formation($validated);
        $formation->user_id = Auth::id(); // On assigne l'utilisateur connecté
        $formation->save();

        return response()->json($formation, 201);
    }

    /**
     * Afficher une formation précise par son ID
     */
    public function show($id)
    {
        $formation = Formation::with([
            'categorie:id,nom',
            'user:id,name,prenom',
            'candidatures.candidat:id,nom,email'
        ])->find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }

        return response()->json($formation, 200);
    }

    /**
     * Mettre à jour une formation existante
     */
    public function update(Request $request, $id)
    {
        Log::info("Données reçues dans update pour l'ID $id :", $request->all());

        $formation = Formation::find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|required|string|max:255',

            'description' => 'nullable|string',
            'public_vise' => 'nullable|string',
            'objectifs' => 'nullable|string',
            'format' => 'nullable|string',
            'certifiante' => 'nullable|boolean',
            'date_debut_candidature' => 'nullable|date',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'date_limite_depot' => 'nullable|date|after_or_equal:date_debut_candidature',
            'heure' => 'nullable|date_format:H:i',
            'duree' => 'nullable|integer|min:1',
            'prix' => 'nullable|numeric|min:0',
            'lieu' => 'nullable|string|max:255',
            'type' => 'nullable|in:Bootcamps,Formations certifiantes,Modules à la carte',
            'id_categorie' => 'sometimes|required|exists:categories,id',
        ]);

        $formation->update($validated);
        $formation->user_id = Auth::id(); // On met à jour l'utilisateur qui modifie
        $formation->save();

        return response()->json($formation, 200);
    }

    /**
     * Supprimer une formation
     */
    public function destroy($id)
    {
        $formation = Formation::find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation non trouvée'], 404);
        }

        $formation->delete();

        return response()->json(['message' => 'Formation supprimée avec succès'], 200);
    }



    // Gestions des mises à jour spécifiques pour webinaire et coaching

public function updateWebinaire(Request $request, $id)
{
    $webinaire = Formation::findOrFail($id);
    $webinaire->update($request->all());

    return response()->json([
        'message' => 'Webinaire mis à jour avec succès',
        'data' => $webinaire
    ]);
}

public function updateCoaching(Request $request, $id)
{
    $coaching = Formation::findOrFail($id);
    $coaching->update($request->all());

    return response()->json([
        'message' => 'Coaching mis à jour avec succès',
        'data' => $coaching
    ]);
}

}
