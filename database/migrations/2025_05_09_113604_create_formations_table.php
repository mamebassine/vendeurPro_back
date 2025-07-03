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
            $table->string('titre'); // Exemple : Devenir Vendeur Pro en 3 jours
            $table->text('description'); // Description longue, programme, etc.

            $table->text('public_vise')->nullable(); // Public visé
            $table->text('objectifs')->nullable(); // Objectifs de la formation
            $table->string('format')->nullable(); // Ex : Hybride, Présentiel, En ligne...
            $table->boolean('certifiante')->default(false); // Si la formation est certifiante

            $table->date('date_debut_candidature')->nullable(); // Pour les candidatures
            $table->date('date_debut')->nullable(); // Date de début de la formation
            $table->date('date_fin')->nullable(); // Date de fin
            $table->date('date_limite_depot')->nullable(); // Dernier jour de dépôt des candidatures
            $table->time('heure')->nullable(); // Heure de la formation

            $table->integer('duree')->nullable(); // Durée en jours ou heures
            $table->decimal('prix', 8, 2)->nullable(); // Tarif
            $table->string('lieu')->nullable(); // En ligne, présentiel...
            $table->enum('type', ['Bootcamps', 'Formations certifiantes', 'Modules à la carte'])->default('Formations certifiantes');

            $table->foreignId('id_categorie')->constrained('categories')->onDelete('cascade'); // Catégorie
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Utilisateur qui a créé

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
