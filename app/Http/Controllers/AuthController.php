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
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|min:9',
            'address' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:user,admin',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            // Enregistre dans storage/app/public/users
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
                return response()->json(['message' => 'Cet email est déjà utilisé.'], 422);
            }
            throw $e;
        }

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user
        ], 201);
    }

    // Méthode de connexion de l'utilisateur
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        return response()->json(compact('token'));
    }

    // Méthode pour obtenir le profil de l'utilisateur authentifié
    public function profile()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user) {
            return response()->json([
                'name' => $user->name,
                'prenom' => $user->prenom,
                'image' => $user->image ? asset('storage/' . $user->image) : null,
            ]);
        }

        return response()->json(['error' => 'Accès refusé'], 403);
    }

    // Méthode de déconnexion
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Déconnexion réussie']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Impossible de déconnecter'], 500);
        }
    }
}
