<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\CategorieController;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\ActualiteController;

use App\Http\Controllers\CommissionController;


Route::get('/actualites', [ActualiteController::class, 'index']);           // Lister toutes les actualités
Route::get('/actualites/{id}', [ActualiteController::class, 'show']);       // Afficher une actualité spécifique

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// 🔓 Inscription d'un nouvel utilisateur
Route::post('register', action: [AuthController::class, 'register']);

// 🔓 Connexion d'un utilisateur (retourne un token)
Route::post('login', action: [AuthController::class, 'login'])->name('login');

       // 📌 Route publique pour candidater à une formation sans compte

// 🔓 Soumission d'une candidature sans avoir de compte utilisateur (ex: visiteur sur le site)
Route::post('public-candidature', [CandidatureController::class, 'storeFromPublic']);

//ICCI MODIFICATIONNNNNNN

// 🔐 Créer un nouveau candidat
               Route::post('candidats', [CandidatController::class, 'store']);
// 🔐 Ajouter une candidature manuellement (par l'admin)
               Route::post('candidatures', [CandidatureController::class, 'store']);

               // 📌 Routes publiques pour consulter les formations


// 🔓 Récupérer toutes les formations disponibles (accueil, page liste)
Route::get('formations', [FormationController::class, 'index']);

// 🔓 Récupérer le détail d'une formation spécifique (page détail)
Route::get('formations/{id}', [FormationController::class, 'show']);


// 🔓 Récupérer les formations de type "different" (page formation )
Route::get('webinaire', [FormationController::class, 'afficherWebinaire']);
Route::get('coaching', [FormationController::class, 'afficherCoaching']);
Route::get('formation', [FormationController::class, 'afficherFormations']);



// 📌 Toutes les routes ci-dessous nécessitent une authentification (token requis)
               Route::middleware('auth:api')->group(function () {

       // Liste globale des candidats parrainés
       Route::get('/candidats-parraines', [AuthController::class, 'listeCandidatsParraines']);

       // Liste des candidats parrainés par un user spécifique
       Route::get('/candidats-parrain/{userId}', [AuthController::class, 'candidatsParrainParUser']);

   Route::get('users', [AuthController::class, 'userList']);


   // Liste des commissions

                 // Liste des commissions (admin)
Route::get('/commissions', [CommissionController::class, 'listeCommissions']);

                 //Liste des commissions du parrain connecté
Route::get('/mes-commissions', [CommissionController::class, 'mesCommissions']);
 

// Création
Route::post('/commissions', [CommissionController::class, 'store']);

// Détail d'une commission
Route::get('/commissions/{id}', [CommissionController::class, 'show']);

// Validation par l'admin
Route::post('/commissions/{id}/valider', [CommissionController::class, 'validerCommission']);

// Suppression
Route::delete('/commissions/{id}', [CommissionController::class, 'destroy']);

// 🔒 Auth connecté
                Route::post('/actualites', [ActualiteController::class, 'store']); // POST OK

                Route::put('/actualites/{id}', [ActualiteController::class, 'update']);     // Mettre à jour une actualité
                Route::delete('/actualites/{id}', [ActualiteController::class, 'destroy']); // Supprimer une actualité



               // 🔐 Récupérer les informations de l'utilisateur connecté
               Route::get('profile', [AuthController::class, 'profile']);

               // 🔐 Déconnexion de l'utilisateur (invalide le token)
               Route::post('logout', [AuthController::class, 'logout']);

               
                              // 🔒 Gestion des formations (admin ou staff connecté uniquement)

               // 🔐 Créer une nouvelle formation
               Route::post('formations', [FormationController::class, 'store']);

               // 🔐 Modifier une formation existante
               Route::put('formations/{id}', [FormationController::class, 'update']);

               // 🔐 Supprimer une formation
               Route::delete('formations/{id}', [FormationController::class, 'destroy']);

// Création d'un coaching ou d'un webinaire
Route::post('ajouter-webinaire', [FormationController::class, 'ajouterWebinaire']);
Route::post('ajouter-coaching', [FormationController::class, 'ajouterCoaching']);
              




               
                              // 🔒 Gestion des catégories (admin uniquement)

               // 🔐 Lister toutes les catégories
               Route::get('categories', [CategorieController::class, 'index']);

               // 🔐 Détail d'une catégorie
               Route::get('categories/{id}', [CategorieController::class, 'show']);

               // 🔐 Créer une nouvelle catégorie
               Route::post('categories', [CategorieController::class, 'store']);

               // 🔐 Modifier une catégorie existante
               Route::put('categories/{id}', [CategorieController::class, 'update']);

               // 🔐 Supprimer une catégorie
               Route::delete('categories/{id}', [CategorieController::class, 'destroy']);



               
                         // 🔒 Gestion des candidatures (consultation et gestion par admin uniquement)

               // 🔐 Lister toutes les candidatures reçues
               Route::get('candidatures', [CandidatureController::class, 'index']);

               // 🔐 Détail d'une candidature spécifique
               Route::get('candidatures/{id}', [CandidatureController::class, 'show']);

               // 🔐 Ajouter une candidature manuellement (par l'admin)
               Route::post('candidatures', [CandidatureController::class, 'store']);

               // 🔐 Modifier une candidature
               Route::put('candidatures/{id}', [CandidatureController::class, 'update']);

               // 🔐 Supprimer une candidature
               Route::delete('candidatures/{id}', [CandidatureController::class, 'destroy']);



                              // 🔒 Gestion des candidats (admin uniquement)

               // 🔐 Lister tous les candidats enregistrés
               Route::get('candidats', [CandidatController::class, 'index']);

               // 🔐 Détail d'un candidat spécifique
               Route::get('candidats/{id}', [CandidatController::class, 'show']);

               

               // 🔐 Modifier un candidat existant
               Route::put('candidats/{id}', [CandidatController::class, 'update']);

               // 🔐 Supprimer un candidat
               Route::delete('candidats/{id}', [CandidatController::class, 'destroy']);
               
            });










