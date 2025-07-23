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
        Schema::create('candidats', function (Blueprint $table) {
        $table->id();
        $table->string(column: 'nom');
        $table->string(column: 'prenom');
        $table->string('email')->unique();
        $table->string('telephone')->unique();
        $table->string('adresse');
        $table->enum('genre', ['homme', 'femme'])->nullable();
        $table->unsignedBigInteger('parrain_id')->nullable();
        $table->foreign('parrain_id')->references('id')->on('candidats')->onDelete('set null');
        $table->string('code_parrainage', 6)->unique()->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidats');
    }
};
