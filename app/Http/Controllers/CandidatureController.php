<?php

namespace App\Http\Controllers;

use App\Models\Candidature;
use Illuminate\Http\Request;

class CandidatureController extends Controller
{
    public function index()
    {
        return response()->json(Candidature::all(), 200);
    }

    public function store(Request $request)
    {
        // Vérifier si une candidature existe déjà pour cette formation et ce candidat
        $existing = Candidature::where('id_formation', $request->input('id_formation'))
            ->where('id_candidat', $request->input('id_candidat'))
            ->first();
    
        if ($existing) {
            return response()->json([
                'message' => 'Candidature déjà existante pour cette formation',
                'candidature' => $existing
            ], 200);
        }
    
        $candidature = Candidature::create($request->all());
        return response()->json($candidature, 201);
    }
    
    

    public function show($id)
    {
        $candidature = Candidature::find($id);
        return $candidature ? response()->json($candidature, 200) : response()->json(['message' => 'Candidature non trouvée'], 404);
    }

    public function update(Request $request, $id)
    {
        $candidature = Candidature::find($id);
        if (!$candidature) return response()->json(['message' => 'Candidature non trouvée'], 404);

        $candidature->update($request->all());
        return response()->json($candidature, 200);
    }

    public function destroy($id)
    {
        $candidature = Candidature::find($id);
        if (!$candidature) return response()->json(['message' => 'Candidature non trouvée'], 404);

        $candidature->delete();
        return response()->json(['message' => 'Candidature supprimée'], 200);
    }
}
