<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Candidat extends Model
{
    use Notifiable;
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'genre', 
    ];
public function formations()
{
    return $this->belongsToMany(Formation::class, 'candidatures', 'id_candidat', 'id_formation')
                ->withPivot('statut')
                ->withTimestamps();
}

   
}
