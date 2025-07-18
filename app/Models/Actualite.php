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
    'user_id',
];

protected $casts = [
    'date_publication' => 'datetime',
    'points' => 'array',
];



// Dans Actualite.php
public function user()
{
    return $this->belongsTo(User::class);
}

}
