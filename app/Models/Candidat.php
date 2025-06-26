<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Candidature;
use App\Models\Commission;
use Illuminate\Support\Facades\DB;
class Candidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'parrain_id',
       'code_parrainage',
    ];
protected static function boot()
{
    parent::boot();

    static::creating(function ($candidat) {
        // Récupérer le dernier code sans le "C"
        $lastCode = self::max(DB::raw("CAST(SUBSTRING(code_parrainage, 2) AS UNSIGNED)"));

        if ($lastCode) {
            $nextNumber = str_pad($lastCode + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '00001';
        }

        $candidat->code_parrainage = 'P' . $nextNumber;
    });
}


    public function parrain()
{
    return $this->belongsTo(Candidat::class, 'parrain_id');
}

public function filleuls()
{
    return $this->hasMany(Candidat::class, 'parrain_id');
}

public function candidatures()
{
    return $this->hasMany(Candidature::class);
}

public function commissionsRecues()
{
    return $this->hasMany(Commission::class, 'parrain_id');
}


}
