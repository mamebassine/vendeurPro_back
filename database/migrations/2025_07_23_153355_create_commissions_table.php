<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
   
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidature_id')->constrained('candidatures')->onDelete('cascade');
            $table->decimal('montant_commission', 8, 2)->default(0);
            $table->string('code_parrainage'); // lien ou code de parrainage
            $table->boolean('commission_versee')->default(false);
            $table->timestamps();
        });
    }

   public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
