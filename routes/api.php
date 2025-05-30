<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\CategorieController;


// Authentification des users

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
                Route::get('profile', [AuthController::class, 'profile']);
                Route::post('logout', [AuthController::class, 'logout']);

            
           Route::get('formations', [FormationController::class, 'index']); 
            Route::get('formations/{id}', [FormationController::class, 'show']); 
            Route::post('formations', [FormationController::class, 'store']); 
            Route::put('formations/{id}', [FormationController::class, 'update']); 
            Route::delete('formations/{id}', [FormationController::class, 'destroy']); 


            Route::get('categories', [CategorieController::class, 'index']);
            Route::get('categories/{id}', [CategorieController::class, 'show']);
            Route::post('categories', [CategorieController::class, 'store']);
            Route::put('categories/{id}', [CategorieController::class, 'update']);
            Route::delete('categories/{id}', [CategorieController::class, 'destroy']);

            Route::get('candidatures', [CandidatureController::class, 'index']); 
            Route::get('candidatures/{id}', [CandidatureController::class, 'show']); 
            Route::post('candidatures', [CandidatureController::class, 'store']);
            Route::put('candidatures/{id}', [CandidatureController::class, 'update']); 
            Route::delete('candidatures/{id}', [CandidatureController::class, 'destroy']);

});


            Route::get('candidats', [CandidatController::class, 'index']); 
            Route::get('candidats/{id}', [CandidatController::class, 'show']); 
            Route::post('candidats', [CandidatController::class, 'store']);
            Route::put('candidats/{id}', [CandidatController::class, 'update']);
            Route::delete('candidats/{id}', [CandidatController::class, 'destroy']);