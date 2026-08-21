<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Models\Room;

class RoomManagementController extends Controller
{
    public function index()
    {
        $rooms = Room::query()
            ->withCount('academicSessions')
            ->orderBy('name')
            ->get();

        return view('rooms.index', compact('rooms'));
    }

    public function store(RoomRequest $request)
    {
        Room::create($request->validated());

        return back()->with('success', 'Salle créée avec succès.');
    }

    public function update(RoomRequest $request, Room $room)
    {
        $room->update($request->validated());

        return back()->with('success', 'Salle mise à jour avec succès.');
    }

    public function destroy(Room $room)
    {
        if ($room->academicSessions()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette salle : des séances lui sont associées.');
        }

        $room->delete();

        return back()->with('success', 'Salle supprimée avec succès.');
    }
}
