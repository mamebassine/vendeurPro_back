<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidature extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_formation',
        'id_candidat',
        'statut'
    ];

    protected $casts = [
        'statut' => 'string'
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'id_formation');
    }

    public function candidat()
    {
        return $this->belongsTo(Candidat::class, 'id_candidat');
    }
}
