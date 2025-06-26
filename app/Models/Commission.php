<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\User;
class Commission extends Model
{
    protected $guarded = [];

     public function parrain()
    {
        return $this->belongsTo(Candidat::class, 'parrain_id');
    }

    public function filleul()
    {
        return $this->belongsTo(Candidat::class, 'filleul_id');
    }

    public function candidature()
    {
        return $this->belongsTo(Candidature::class, 'candidature_id');
    }
      public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
