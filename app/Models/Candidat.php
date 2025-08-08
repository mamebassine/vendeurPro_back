<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Candidat extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'genre',
        'code_parrainage',
        
    ];

    // Formations via candidatures
    public function formations()
    {
        return $this->belongsToMany(Formation::class, 'candidatures', 'id_candidat', 'id_formation')
                    ->withPivot('statut')
                    ->withTimestamps();
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'id_candidat');
    }
}
