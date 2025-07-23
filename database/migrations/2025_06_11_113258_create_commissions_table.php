<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('commissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('parrain_id')->constrained('candidats')->onDelete('cascade');
    $table->foreignId('filleul_id')->constrained('candidats')->onDelete('cascade');
    $table->foreignId('candidature_id')->constrained('candidatures')->onDelete('cascade');
   $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
    $table->decimal('montant_commission', 10, 4);
    // Date de saisie, par défaut la date du jour
    $table->dateTime('date_commission')->default(DB::raw('NOW()'));
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
