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

// public function listeCandidatsParraines(Request $request)
// {
//     $users = User::with(['candidaturesParrain.candidat', 'candidaturesParrain.formation'])
//         ->whereHas('candidaturesParrain')
//         ->get();

//     return response()->json([
//         'success' => true,
//         'users' => $users
//     ]);
// }
// public function listeCandidatsParraines(Request $request)
// {
//     // Authentifier l'utilisateur connecté
//     $user = JWTAuth::parseToken()->authenticate();

//     // Vérifier que l'utilisateur est un parrain
//     if ($user->role !== 'user') {
//         return response()->json([
//             'success' => false,
//             'message' => 'Accès non autorisé. Seuls les parrains peuvent accéder à cette ressource.'
//         ], 403);
//     }

//     // Charger uniquement ses candidatures parrainées
//     $user->load([
//         'candidaturesParrain.candidat',
//         'candidaturesParrain.formation',
//     ]);

//     return response()->json([
//         'success' => true,
//         'users' => [$user] // tableau avec un seul parrain et ses filleuls
//     ]);
// }


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




// Méthode pour inscrire un utilisateur
    public function register(Request $request)
    {
        // Validation des données avec messages personnalisés
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|min:9',
            'address' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:admin,user',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'code_parrainage' => 'nullable|string|exists:users,code_parrainage', // Vérifie si le code existe chez un user

        ], [
            'name.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'role.in' => 'Le rôle sélectionné est invalide.',
            'image.image' => 'Le fichier doit être une image.',
            'image.mimes' => 'L\'image doit être au format jpeg, png ou jpg.',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
        }
try {
            DB::beginTransaction();

// Forcer le rôle à 'user' si quelqu’un tente d’inscrire un 'admin'
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
            ]);

            // Vérifie s’il y a un code parrainage
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

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur inscrit avec succès.',
                'data' => $user
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


}
