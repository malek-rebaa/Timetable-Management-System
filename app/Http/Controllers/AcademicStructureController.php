<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassRoomRequest;
use App\Http\Requests\LevelRequest;
use App\Models\ClassRoom;
use App\Models\Level;

class AcademicStructureController extends Controller
{
    public function index()
    {
        $levels = Level::query()
            ->withCount(['classRooms', 'subjectPlans'])
            ->orderBy('name')
            ->get();

        $classRooms = ClassRoom::query()
            ->with('level')
            ->withCount('academicSessions')
            ->orderBy('name')
            ->get();

        return view('levels.index', compact('levels', 'classRooms'));
    }

    public function store(LevelRequest $request)
    {
        Level::create($request->validated());

        return back()->with('success', 'Niveau créé avec succès.');
    }

    public function update(LevelRequest $request, Level $level)
    {
        $level->update($request->validated());

        return back()->with('success', 'Niveau mis à jour avec succès.');
    }

    public function destroy(Level $level)
    {
        if ($level->classRooms()->exists() || $level->subjectPlans()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce niveau : il possède encore des classes ou des programmes.');
        }

        $level->delete();

        return back()->with('success', 'Niveau supprimé avec succès.');
    }

    public function storeClass(ClassRoomRequest $request)
    {
        ClassRoom::create($request->validated());

        return back()->with('success', 'Classe créée avec succès.');
    }

    public function updateClass(ClassRoomRequest $request, ClassRoom $classRoom)
    {
        $classRoom->update($request->validated());

        return back()->with('success', 'Classe mise à jour avec succès.');
    }

    public function destroyClass(ClassRoom $classRoom)
    {
        if ($classRoom->academicSessions()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette classe : des séances lui sont associées.');
        }

        $classRoom->delete();

        return back()->with('success', 'Classe supprimée avec succès.');
    }
}
