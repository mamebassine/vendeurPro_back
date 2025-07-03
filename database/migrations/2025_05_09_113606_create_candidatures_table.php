<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_formation')->constrained('formations')->onDelete('cascade');
            $table->foreignId('id_candidat')->constrained('candidats')->onDelete('cascade');
            $table->enum('statut', allowed: ['en attente', 'acceptée', 'refusée']);
            $table->timestamps();

            // ✅ Contrainte pour éviter les doublons d'une même candidature
            $table->unique(['id_formation', 'id_candidat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
