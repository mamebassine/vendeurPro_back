<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActualiteController;

// Routes publiques (sans authentification)

// Actualités publiques
Route::get('/actualites', [ActualiteController::class, 'index']);           
Route::get('/actualites/{id}', [ActualiteController::class, 'show']);       

// Dashboard stats publiques (si besoin)
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Authentification publique
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');

// Candidature publique sans compte utilisateur
Route::post('public-candidature', [CandidatureController::class, 'storeFromPublic']);

// Formations publiques (liste et détail + types spécifiques)
Route::get('formations', [FormationController::class, 'index']);
Route::get('formations/{id}', [FormationController::class, 'show']);
Route::get('webinaire', [FormationController::class, 'afficherWebinaire']);
Route::get('coaching', [FormationController::class, 'afficherCoaching']);
Route::get('formation', [FormationController::class, 'afficherFormations']);

// Routes protégées (auth:api)
Route::middleware('auth:api')->group(function () {

    // Actualités (création, modification, suppression)
    Route::post('/actualites', [ActualiteController::class, 'store']);
    Route::put('/actualites/{id}', [ActualiteController::class, 'update']);
    Route::delete('/actualites/{id}', [ActualiteController::class, 'destroy']);

    // Profil et logout
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Formations (gestion)
    Route::post('formations', [FormationController::class, 'store']);
    Route::put('formations/{id}', [FormationController::class, 'update']);
    Route::delete('formations/{id}', [FormationController::class, 'destroy']);
    Route::post('ajouter-webinaire', [FormationController::class, 'ajouterWebinaire']);
    Route::post('ajouter-coaching', [FormationController::class, 'ajouterCoaching']);

    // Catégories (gestion)
    Route::get('categories', [CategorieController::class, 'index']);
    Route::get('categories/{id}', [CategorieController::class, 'show']);
    Route::post('categories', [CategorieController::class, 'store']);
    Route::put('categories/{id}', [CategorieController::class, 'update']);
    Route::delete('categories/{id}', [CategorieController::class, 'destroy']);

    // Candidatures (gestion)
    Route::get('candidatures', [CandidatureController::class, 'index']);
    Route::get('candidatures/{id}', [CandidatureController::class, 'show']);
    Route::post('candidatures', [CandidatureController::class, 'store']);
    Route::put('candidatures/{id}', [CandidatureController::class, 'update']);
    Route::delete('candidatures/{id}', [CandidatureController::class, 'destroy']);

    // Candidats (gestion)
    Route::get('candidats', [CandidatController::class, 'index']);
    Route::get('candidats/{id}', [CandidatController::class, 'show']);
    Route::post('candidats', [CandidatController::class, 'store']);
    Route::put('candidats/{id}', [CandidatController::class, 'update']);
    Route::delete('candidats/{id}', [CandidatController::class, 'destroy']);

    // Commissions (gestion)
    // Route::post('/ajouter/commissions', [CommissionController::class, 'store']);
    // Route::put('/modifier/commissions/{id}', [CommissionController::class, 'update']);
    Route::get('commissions/{id}', [CommissionController::class, 'show']);
    Route::get('/lister/commissions', [CommissionController::class, 'index']);
    Route::delete('/supprimer/commissions/{id}', [CommissionController::class, 'destroy']);
    Route::get('/parrains/commissions/{parrainId}', [CommissionController::class, 'indexParrainCommissions']);

});
