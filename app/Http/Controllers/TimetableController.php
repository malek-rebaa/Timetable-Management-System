<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicSessionRequest;
use App\Jobs\GenerateTimetableJob;
use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\SubjectPlan;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\SlotGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    /**
     * Affiche l'emploi du temps avec filtres.
     */
    public function index(Request $request)
    {
        $classRooms = ClassRoom::with('level')->get();
        $teachers = User::where('role', 'TEACHER')->get();
        $timetables = Timetable::orderBy('created_at', 'desc')->get();
        $subjectPlans = SubjectPlan::with(['subject', 'level'])->get();
        $selectedTimetable = $request->filled('timetable_id')
            ? Timetable::find($request->timetable_id)
            : null;

        $sessions = AcademicSession::query()
            ->with(['subjectPlan.subject', 'teacher', 'classRoom.level', 'room'])
            ->when($request->user()->role === 'TEACHER', function ($q) use ($request) {
                $q->where('teacher_id', $request->user()->id);
            })
            ->when($request->filled('timetable_id'), function ($q) use ($request) {
                $q->where('timetable_id', $request->timetable_id);
            })
            ->when($request->filled('filter_class'), function ($q) use ($request) {
                $q->where('class_room_id', $request->filter_class);
            })
            ->when($request->filled('filter_teacher'), function ($q) use ($request) {
                $q->where('teacher_id', $request->filter_teacher);
            })
            ->when($request->filled('filter_room'), function ($q) use ($request) {
                $q->where('room_id', $request->filter_room);
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        $rooms = \App\Models\Room::all();

        return view('timetable.index', compact(
            'sessions',
            'classRooms',
            'teachers',
            'timetables',
            'subjectPlans',
            'rooms',
            'selectedTimetable'
        ));
    }

    /**
     * Crée une séance manuellement.
     */
    public function storeSession(StoreAcademicSessionRequest $request)
    {
        AcademicSession::create($request->validated());

        return redirect()->route('timetable.index')
            ->with('generation_success', 'Séance ajoutée avec succès.');
    }

    /**
     * Met à jour une séance.
     */
    public function updateSession(StoreAcademicSessionRequest $request, AcademicSession $session)
    {
        if ($session->is_locked) {
            return back()->withErrors(['conflict' => 'Cette séance est verrouillée et ne peut pas être modifiée.']);
        }

        $session->update($request->validated());

        return redirect()->route('timetable.index')
            ->with('generation_success', 'Séance modifiée avec succès.');
    }

    /**
     * Supprime une séance.
     */
    public function destroySession(AcademicSession $session)
    {
        if ($session->is_locked) {
            return back()->withErrors(['conflict' => 'Cette séance est verrouillée et ne peut pas être supprimée.']);
        }

        $session->delete();

        return redirect()->route('timetable.index')
            ->with('generation_success', 'Séance supprimée.');
    }

    /**
     * Verrouille/déverrouille une séance.
     */
    public function toggleLock(AcademicSession $session)
    {
        $session->is_locked = ! $session->is_locked;
        $session->save();

        return redirect()->route('timetable.index')
            ->with('generation_success', $session->is_locked ? 'Séance verrouillée.' : 'Séance déverrouillée.');
    }

    /**
     * Lance la génération automatique (asynchrone).
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string'],
            'semester' => ['nullable', 'string'],
            'timetable_id' => ['nullable', 'exists:timetables,id'],
            'filter_class' => ['nullable', 'exists:class_rooms,id'],
        ]);

        $timetableId = $validated['timetable_id'] ?? null;
        $classRoomId = $validated['filter_class'] ?? null;
        
        $timetable = $timetableId
            ? Timetable::findOrFail($timetableId)
            : Timetable::create([
                'name' => $validated['name'] ?? 'EDT ' . now()->format('Y-m-d H:i'),
                'academic_year' => $validated['academic_year'] ?? null,
                'semester' => $validated['semester'] ?? null,
                'status' => 'PENDING',
            ]);

        $timetable->status = 'PENDING';
        $timetable->save();
        $timetableId = $timetable->id;

        $options = [
            'name' => $validated['name'] ?? null,
            'academic_year' => $validated['academic_year'] ?? null,
            'semester' => $validated['semester'] ?? null,
        ];

        if ($classRoomId) {
            $options['class_room_id'] = (int) $classRoomId;
        }

        GenerateTimetableJob::dispatch($timetableId, $options);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Génération mise en file d'attente.",
                'timetable_id' => $timetableId,
                'status' => 'PENDING',
            ], 202);
        }

        return redirect()->route('timetable.index', ['timetable_id' => $timetableId])
            ->with('generation_success', "Génération mise en file d'attente.");
    }

    /**
     * Supprime un emploi du temps et toutes ses séances, même verrouillées.
     */
    public function destroyTimetable(Timetable $timetable)
    {
        DB::transaction(function () use ($timetable) {
            $timetable->academicSessions()->delete();
            $timetable->delete();
        });

        return redirect()->route('timetable.index')
            ->with('generation_success', 'Emploi du temps supprimé.');
    }
}
