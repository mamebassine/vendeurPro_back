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
        'code_parrainage',
        'statut',
    ];

    protected $casts = [
        'statut' => 'string'
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class, 'id_formation', 'id');
    }

    public function candidat()
    {
        return $this->belongsTo(Candidat::class, 'id_candidat', 'id');
    }

   public function user()
    {
            return $this->belongsTo(User::class, 'code_parrainage', 'code_parrainage');

        // return $this->belongsTo(User::class, 'id_user', 'id');
    }





    public function commission()
    {
        return $this->hasOne(Commission::class); // Une candidature a une seule commission
    }
    
}
