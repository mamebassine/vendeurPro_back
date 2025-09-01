<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\Actualite;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'formations' => Formation::count(),
            'candidats' => Candidat::count(),
            'candidatures' => Candidature::count(),
            // 'actualites' => Actualite::count()
        ]);
    }
}
