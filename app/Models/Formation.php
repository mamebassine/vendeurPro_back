<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
    'titre', 'description', 'public_vise', 'objectifs', 'format', 'certifiante',
    'date_debut_candidature', 'date_debut', 'date_fin', 'date_limite_depot', 'heure',
    'duree', 'prix', 'lieu', 'type', 'id_categorie', 'user_id',
];


    protected $casts = [
        'date_debut_candidature' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_limite_depot' => 'date',
        'prix' => 'decimal:2',
        'date_heure' => 'string', // ou 'datetime:H:i' si tu utilises Carbon

    ];

    // Si tu veux garder un format plus lisible de l'heure dans ton JSON
    protected $appends = ['heure_formate'];

public function getHeureFormateAttribute()
{
    return $this->heure ? Carbon::parse($this->heure)->format('H\hi') : null;
}


    public function categorie()
    {
        return $this->belongsTo(Categorie::class, foreignKey: 'id_categorie');
    }

    public function user()
{
    return $this->belongsTo(User::class, foreignKey: 'user_id');
}

}
