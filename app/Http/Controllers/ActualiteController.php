<?php

namespace App\Http\Controllers;

use App\Models\Actualite;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActualiteController extends Controller
{
    // Liste toutes les actualités
    public function index()
    {
        return response()->json(Actualite::all(), 200);
    }

    // Crée une nouvelle actualité
//    public function store(Request $request)
// {
//     $request->validate([
//         'titre' => 'required|string|max:255',
//         // 'date_publication' => 'required|date',
//         'contenu' => 'required|string',
//         'auteur' => 'required|string|max:255',
//         'fonction' => 'required|string|max:255',
//         'points' => 'nullable|string',
//         'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
//         'conclusion' => 'nullable|string',

//     ]);

//     // Authentification sécurisée
//     $user = Auth::user();
//     if (!$user) {
//         return response()->json(['message' => 'Utilisateur non authentifié'], 401);
//     }

//     // Sauvegarde de l'image
//     $imagePath = $request->file('image')->store('actualites', 'public');

//     $actualite = Actualite::create([
//         'titre' => $request->titre,
//         // 'date_publication' => $request->date_publication,
//         'date_publication' => Carbon::now(), // 🔥 ici on met la date automatique

//         'contenu' => $request->contenu,
//         'auteur' => $request->auteur,
//         'fonction' => $request->fonction,
//         'image' => $imagePath,
//         'points' => $request->points,
//         //'points' => $request->points ? json_encode(json_decode($request->points, true)) : null,
//         'conclusion' => $request->conclusion,
//         'user_id' => $user->id,
//     ]);

//     return response()->json([
//         'message' => 'Actualité ajoutée avec succès',
//         'data' => $actualite,
//     ], 201);
// }


public function store(Request $request)
{
    $validated = $request->validate([
        'titre' => 'required|string|max:255',
        'contenu' => 'required|string',
        'auteur' => 'nullable|string',
        'fonction' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'date_publication' => 'nullable|date',
        'points' => 'nullable|array',
        'conclusion' => 'nullable|string',
        'user_id' => 'nullable|exists:users,id',
    ]);

    // ✅ Upload de l'image si présente
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('actualites', 'public');
        $validated['image'] = $path;
    }

    $actualite = Actualite::create($validated);

    return response()->json($actualite, 201);
}



    // Met à jour une actualité existante
    public function update(Request $request, $id)
    {
        $actualite = Actualite::findOrFail($id);

        $request->validate([
            'titre' => 'required|string|max:255',
            'date_publication' => 'required|date',
            'contenu' => 'required|string',
            'auteur' => 'required|string|max:255',
            'fonction' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'points' => 'nullable|string',

        ]);

        // Traitement de l’image si une nouvelle est envoyée
        if ($request->hasFile('image')) {
            // Supprime l’ancienne image si elle existe
            if ($actualite->image && Storage::disk('public')->exists($actualite->image)) {
                Storage::disk('public')->delete($actualite->image);
            }

            $actualite->image = $request->file('image')->store('actualites', 'public');
        }

        // Mise à jour des autres champs
        $actualite->update([
            'titre' => $request->titre,
            'date_publication' => $request->date_publication,
            'contenu' => $request->contenu,
            'auteur' => $request->auteur,
            'fonction' => $request->fonction,
             'points' => $request->points,
            //'points' => $request->points ? json_encode(json_decode($request->points, true)) : null,

            'conclusion' => $request->conclusion,
            'image' => $actualite->image,
        ]);

        return response()->json([
            'message' => 'Actualité mise à jour avec succès',
            'data' => $actualite,
        ]);
    }

    // Affiche une actualité spécifique
    public function show($id)
    {
        $actualite = Actualite::find($id);

        if (!$actualite) {
            return response()->json(['message' => 'Actualité non trouvée'], 404);
        }

        return response()->json($actualite);
    }

    // Supprime une actualité
    public function destroy($id)
    {
        $actualite = Actualite::find($id);

        if (!$actualite) {
            return response()->json(['message' => 'Actualité non trouvée'], 404);
        }

        // Supprime l'image associée si elle existe
        if ($actualite->image && Storage::disk('public')->exists($actualite->image)) {
            Storage::disk('public')->delete($actualite->image);
        }

        $actualite->delete();

        return response()->json(['message' => 'Actualité supprimée avec succès']);
    }
}
