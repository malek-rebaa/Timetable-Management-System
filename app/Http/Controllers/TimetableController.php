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

        try {
            $generator = app(\App\Services\Timetable\TimetableGenerator::class);
            $options = [
                'name' => $validated['name'] ?? null,
                'academic_year' => $validated['academic_year'] ?? null,
                'semester' => $validated['semester'] ?? null,
            ];
            
            if ($classRoomId) {
                $options['class_room_ids'] = [(int) $classRoomId];
            }

            $result = $generator->generate($timetable, $options);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'Génération réussie' : 'Échec de la génération',
                    'timetable_id' => $timetableId,
                    'status' => $timetable->status,
                    'errors' => $result['errors'] ?? [],
                    'diagnostics' => $result['diagnostics'] ?? [],
                ]);
            }

            if ($result['success']) {
                return redirect()->route('timetable.index', ['timetable_id' => $timetableId])
                    ->with('generation_success', "Génération réussie : {$result['placed']} séances placées.");
            } else {
                return redirect()->route('timetable.index', ['timetable_id' => $timetableId])
                    ->with('generation_errors', $result['errors']);
            }
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Erreur lors de la génération : ' . $e->getMessage()]
                ], 500);
            }

            return redirect()->route('timetable.index')
                ->with('generation_errors', ['Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Supprime un emploi du temps (ses séances non verrouillées).
     */
    public function destroyTimetable(Timetable $timetable)
    {
        $commitService = app(\App\Services\Timetable\TimetableCommitService::class);
        $commitService->clearTimetable($timetable);
        $timetable->delete();

        return redirect()->route('timetable.index')
            ->with('generation_success', 'Emploi du temps supprimé.');
    }
}