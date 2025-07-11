<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
// Champs communs
            $table->string('titre'); // Obligatoire pour toutes les catégories
            $table->time('heure')->nullable(); // Webinaire, Formation
            $table->integer('duree')->nullable(); // Toutes catégories
            $table->foreignId('id_categorie')->constrained('categories')->onDelete('cascade'); // obligatoire
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Champs spécifiques à "formation"
            $table->text('description')->nullable(); 
            $table->text('public_vise')->nullable(); 
            $table->text('objectifs')->nullable(); 
            $table->string('format')->nullable(); 
            $table->boolean('certifiante')->nullable(); 
            $table->date('date_debut_candidature')->nullable(); 
            $table->date('date_debut')->nullable(); 
            $table->date('date_fin')->nullable(); 
            $table->date('date_limite_depot')->nullable(); 
            $table->decimal('prix', 8, 2)->nullable(); 
            $table->string('lieu')->nullable(); 
            $table->enum('type', ['Bootcamps', 'Formations certifiantes', 'Modules à la carte'])->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
