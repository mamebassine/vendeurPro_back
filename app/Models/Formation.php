<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'date_debut_candidature',
        'date_debut',
        'date_fin',
        'date_limite_depot',
        'date_heure',
        'duree',
        'prix',
        'lieu',
        'id_categorie',
    ];

    protected $casts = [
        'date_debut_candidature' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_limite_depot' => 'date',
        'date_heure' => 'datetime',
        'prix' => 'decimal:2',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'id_categorie');
    }
}
