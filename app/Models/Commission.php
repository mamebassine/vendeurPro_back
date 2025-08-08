<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidature_id',
        'montant_commission',
        'code_parrainage',
        'commission_versee',
    ];
    public function candidature()
    {
        return $this->belongsTo(Candidature::class);
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
