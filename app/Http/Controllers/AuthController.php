<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
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
            'role' => 'nullable|string|in:user', // Bloque l'inscription directe comme admin
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            $user = User::create([
                'name' => $request->name,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => $request->role ?? 'user',
                'image' => $imagePath,
            ]);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return response()->json(['success' => false, 'message' => 'Cet email est déjà utilisé.'], 422);
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user
        ], 201);
    }

    // Méthode de connexion de l'utilisateur
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

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => JWTAuth::user()
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
}
