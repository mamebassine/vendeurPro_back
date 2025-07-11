<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actualite extends Model
{
    protected $fillable = [
    'titre',
    'contenu',
    'auteur',
    'fonction',
    'image',
    'date_publication',
    'points',
    'conclusion',
];

protected $casts = [
    'date_publication' => 'datetime',
    'points' => 'array',
];

}
