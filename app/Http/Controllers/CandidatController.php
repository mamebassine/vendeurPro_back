<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use Illuminate\Http\Request;

class CandidatController extends Controller
{
    public function index()
    {
        return response()->json(Candidat::all(), 200);
    }
////ca cest ta focntion store que vous avez fait
    public function stores(Request $request)
    {
        $candidat = Candidat::create($request->all());
        return response()->json($candidat, 201);
    }

//////ca cest ma fonction que j'ai crée et j'ai ajouté deux champs parrain_id et code_parrainage
    public function store(Request $request)
{
    // 1. Validation des champs
    $request->validate([
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'email' => 'required|email|unique:candidats,email',
        'telephone' => 'required|string|unique:candidats,telephone',
        'adresse' => 'required|string',
        'genre' => 'nullable|in:homme,femme',
        'code_parrain' => 'nullable|string|size:6|exists:candidats,code_parrainage', // vérifie si le code du parrain existe
    ]);

    // 2. Générer automatiquement le code du candidat
    $candidat = new Candidat([
    'nom' => $request->nom,
    'prenom' => $request->prenom,
    'email' => $request->email,
    'telephone' => $request->telephone,
    'adresse' => $request->adresse,
    'genre' => $request->genre,
    'parraind_id'=>$request->parrain_id,
]);

    // Si un code parrain est fourni
    if ($request->filled('code_parrain')) {
        $parrain = Candidat::where('code_parrainage', $request->code_parrain)->first();
        if ($parrain) {
            $candidat->parrain_id = $parrain->id;
        }
    }

    $candidat->save();

    return response()->json([
        'message' => 'Candidat enregistré avec succès.',
        'candidat' => $candidat
    ], 201);
}

//// ca aussi cest ma focntion update
public function update(Request $request, $id)
{
    // 2. Valider les données
    $request->validate([
        'nom' => 'sometimes|required|string',
        'prenom' => 'sometimes|required|string',
        'email' => 'sometimes|required|email|unique:candidats,email,' . $id,
        'telephone' => 'sometimes|required|string|unique:candidats,telephone,' . $id,
        'adresse' => 'sometimes|required|string',
        'genre' => 'nullable|in:homme,femme',
        'code_parrain' => 'nullable|string|size:6|exists:candidats,code_parrainage',
    ]);
    // 3. Mettre à jour les champs simples
    $candidat = Candidat::findOrFail($id);
    $candidat->nom = $request->nom;
    $candidat->prenom = $request->prenom;
    $candidat->email = $request->email;
    $candidat->telephone = $request->telephone;
    $candidat->adresse = $request->adresse;
    $candidat->genre = $request->genre;
    $candidat->parrain_id= $request->parrain_id;
    // 4. Mise à jour du parrain si un code est fourni
    if ($request->filled('code_parrain')) {
        $parrain = Candidat::where('code_parrainage', $request->code_parrain)->first();
        if ($parrain) {
            $candidat->parrain_id = $parrain->id;
        }
    }
    // 5. Enregistrer les changements
    $candidat->save();
    // 6. Retourner la réponse
    return response()->json([
        'message' => 'Candidat mis à jour avec succès.',
        'candidat' => $candidat
    ], 200);
}

public function show($id)
 {
        $candidat = Candidat::find($id);
        return $candidat ? response()->json($candidat, 200) : response()->json(['message' => 'Candidat non trouvé'], 404);
}

    public function updates(Request $request, $id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) return response()->json(['message' => 'Candidat non trouvé'], 404);

        $candidat->update($request->all());
        return response()->json($candidat, 200);
    }

    public function destroy($id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) return response()->json(['message' => 'Candidat non trouvé'], 404);

        $candidat->delete();
        return response()->json(['message' => 'Candidat supprimé'], 200);
    }
}
