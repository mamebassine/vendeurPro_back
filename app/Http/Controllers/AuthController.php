<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Méthode pour inscrire un utilisateur
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',  
            'phone' => 'required|string|min:9',
            'address' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:user,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role ?? 'user',
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user]);
    }

    // Méthode de connexion de l'utilisateur
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Vérifie que l'utilisateur existe et génère un token
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
            return response()->json($user);
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
