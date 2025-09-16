<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Candidat;
use App\Models\Commission;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
/**
     * Liste globale des candidats parrainés par tous les users (parrain)
     */
public function candidatsParrainParUser($userId)
{
    $user = User::where('role', 'user')
        ->with(['candidaturesParrain', 'commissions'])
        ->findOrFail($userId);

    $solde = $user->commissions->where('commission_versee', false)->sum('montant_commission');
    $total_commissions = $user->commissions->sum('montant_commission');

    return response()->json([
        'success' => true,
        'parrain' => $user->name . ' ' . $user->prenom,
        'solde_commissions_non_versees' => $solde,
        'total_commissions' => $total_commissions,
        'candidats' => $user->candidaturesParrain
    ]);
}

// Liste des candidats parrainés par l'utilisateur connecté
public function listeCandidatsParraines(Request $request)
{
    // Authentifier l'utilisateur connecté avec le token
    $user = JWTAuth::parseToken()->authenticate();

    // Vérifier que l'utilisateur est bien un parrain
    if ($user->role !== 'user') {
        return response()->json([
            'success' => false,
            'message' => 'Accès non autorisé. Seuls les parrains peuvent accéder à cette ressource.'
        ], 403);
    }

    // Charger uniquement ses candidatures parrainées
    $user->load([
        'candidaturesParrain.candidat',
        'candidaturesParrain.formation',
        'candidaturesParrain.commissions'
    ]);

    return response()->json([
        'success' => true,
        'parrain' => $user->name . ' ' . $user->prenom,
        'fillieuls' => $user->candidaturesParrain
    ]);
}


// public function monLienParrainage()
// {
//     // Récupérer le parrain connecté
//     $user = JWTAuth::parseToken()->authenticate();

//     // Vérifier que c'est bien un parrain
//     if ($user->role !== 'user') {
//         return response()->json([
//             'success' => false,
//             'message' => 'Accès non autorisé. Seuls les parrains peuvent accéder à cette ressource.'
//         ], 403);
//     }

//     // Vérifier que le code existe (au cas où)
//     if (!$user->code_parrainage) {
//         $user->code_parrainage = Str::upper(Str::random(10));
//         $user->save();
//     }

//     // Générer le lien de parrainage
//     $lienParrainage = url('/register?code=' . $user->code_parrainage);

//     return response()->json([
//         'success' => true,
//         'code_parrainage' => $user->code_parrainage,
//         'lien_parrainage' => $lienParrainage
//     ]);
// }

public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'prenom' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'phone' => 'required|string|min:9',
        'address' => 'nullable|string|max:255',
        'role' => 'nullable|string|in:admin,user',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'code_parrainage' => 'nullable|string|exists:users,code_parrainage',
    ]);

    $imagePath = $request->hasFile('image') ? $request->file('image')->store('users', 'public') : null;

    try {
        DB::beginTransaction();

        $role = $request->role === 'admin' ? 'user' : ($request->role ?? 'user');

       // Génération unique du code de parrainage
        do {
            $codeParrainage = Str::upper(Str::random(10));
        } while (User::where('code_parrainage', $codeParrainage)->exists());

        $user = User::create([
            'name' => $request->name,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $role,
            'image' => $imagePath,
            'code_parrainage' => $codeParrainage,
            'solde' => 0,
        // 'lien_parrainage' => $lienParrainage,

        ]);

        // Vérifie s’il y a un parrain existant
        if ($request->filled('code_parrainage')) {
            $parrain = User::where('code_parrainage', $request->code_parrainage)->first();

            if ($parrain) {
                Commission::create([
                    'candidature_id' => null,
                    'montant_commission' => 0,
                    'code_parrainage' => $request->code_parrainage,
                    'commission_versee' => false,
                ]);
            }
        }

       DB::commit();

// Retour JSON en utilisant uniquement le lien stocké
return response()->json([
    'success' => true,
    'message' => 'Utilisateur inscrit avec succès.',
    'data' => $user,  // Contient code_parrainage + lien_parrainage généré
    // 'code_parrainage' => $user->code_parrainage,
    // 'lien_parrainage' => $user->lien_parrainage
], 201);


    } catch (QueryException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'inscription.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    // ICI METHODE DE CONNEXION
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    try {
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['success' => false, 'error' => 'Identifiants invalides'], 401);
        }
    } catch (JWTException $e) {
        return response()->json(['success' => false, 'error' => 'Erreur lors de la tentative de connexion'], 500);
    }

    $user = JWTAuth::user();

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'prenom' => $user->prenom,
            'email' => $user->email,
            'role' => $user->role,
            'image' => $user->image ? url('storage/' . $user->image) : null,
        ],
    ]);
}


    // Méthode pour obtenir le profil de l'utilisateur authentifié
    public function profile()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'prenom' => $user->prenom,
                    'image' => $user->image ? url('storage/' . $user->image) : null,
                    'role' => $user->role,
                                ]
            ]);
        }

        return response()->json(['success' => false, 'error' => 'Accès refusé'], 403);
    }

    // Méthode de déconnexion
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success' => true, 'message' => 'Déconnexion réussie']);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'error' => 'Impossible de déconnecter'], 500);
        }
    }

public function userList()
{
    $users = User::where('role', 'user')
        ->with([
            'candidaturesParrain.formation',
            'candidaturesParrain.candidat',
            'candidaturesParrain.commissions'
        ])
        ->get();

    return response()->json([
        'success' => true,
        'users' => $users
    ]);
}





/**
 * Mettre à jour un utilisateur existant
 */
public function updateUser(Request $request, $id)
{
    // Récupère l'utilisateur à modifier
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    // Validation des champs
    $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'prenom' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|nullable|string|min:8',
        'phone' => 'sometimes|required|string|min:9',
        'address' => 'nullable|string|max:255',
        'role' => 'nullable|string|in:admin,user',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // Commence la transaction
    try {
        DB::beginTransaction();

        // Mettre à jour les champs simples
        $user->name = $request->name ?? $user->name;
        $user->prenom = $request->prenom ?? $user->prenom;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->address = $request->address ?? $user->address;
        $user->role = $request->role ?? $user->role;

        // Mettre à jour le mot de passe si fourni
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        // Mettre à jour l'image si fournie
        if ($request->hasFile('image')) {
            $user->image = $request->file('image')->store('users', 'public');
        }

        $user->save();
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès.',
            'data' => $user
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
