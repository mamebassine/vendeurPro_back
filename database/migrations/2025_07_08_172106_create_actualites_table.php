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
        Schema::create('actualites', function (Blueprint $table) {
         $table->id();
        $table->string('titre');
        $table->text('contenu'); // texte principal introductif
        $table->string('auteur');
        $table->string('fonction')->nullable();
        $table->string('image')->nullable();
        $table->dateTime('date_publication');
        $table->json('points')->nullable(); // liste de blocs
        $table->text('conclusion')->nullable(); // paragraphe final
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualites');
    }
};
