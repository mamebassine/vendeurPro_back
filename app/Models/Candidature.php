<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Candidat;
use App\Models\Formation;
use App\Models\Commission;
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
    return $this->belongsTo(Formation::class, 'id_formation', 'id');
    }

    public function candidat()
    {
        return $this->belongsTo(Candidat::class, 'id_candidat', 'id');
    }

   public function commissionsRecues()
{
    return $this->hasMany(Commission::class, 'parrain_id');
}

public function commission() {
    return $this->hasOne(Commission::class, 'candidature_id');
}
}
