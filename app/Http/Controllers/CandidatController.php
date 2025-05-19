<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use Illuminate\Http\Request;

class CandidatController extends Controller
{
    public function index()
    {
        return response()->json(Candidat::all(), 200);
    }

    public function store(Request $request)
    {
        $candidat = Candidat::create($request->all());
        return response()->json($candidat, 201);
    }

    public function show($id)
    {
        $candidat = Candidat::find($id);
        return $candidat ? response()->json($candidat, 200) : response()->json(['message' => 'Candidat non trouvé'], 404);
    }

    public function update(Request $request, $id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) return response()->json(['message' => 'Candidat non trouvé'], 404);

        $candidat->update($request->all());
        return response()->json($candidat, 200);
    }

    public function destroy($id)
    {
        $candidat = Candidat::find($id);
        if (!$candidat) return response()->json(['message' => 'Candidat non trouvé'], 404);

        $candidat->delete();
        return response()->json(['message' => 'Candidat supprimé'], 200);
    }
}
