<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectPlanRequest;
use App\Http\Requests\SubjectRequest;
use App\Models\Level;
use App\Models\Subject;
use App\Models\SubjectPlan;

class SubjectManagementController extends Controller
{
    public function index()
    {
        $subjects = Subject::query()
            ->withCount(['teachers', 'subjectPlans'])
            ->orderBy('name')
            ->get();

        $levels = Level::query()->orderBy('name')->get();

        $subjectPlans = SubjectPlan::query()
            ->with(['level', 'subject'])
            ->withCount('academicSessions')
            ->orderBy('level_id')
            ->orderBy('subject_id')
            ->get();

        return view('subjects.index', compact('subjects', 'levels', 'subjectPlans'));
    }

    public function store(SubjectRequest $request)
    {
        Subject::create($request->validated());

        return back()->with('success', 'Matière créée avec succès.');
    }

    public function update(SubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());

        return back()->with('success', 'Matière mise à jour avec succès.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->subjectPlans()->exists() || $subject->teachers()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette matière : elle est utilisée par un programme ou un enseignant.');
        }

        $subject->delete();

        return back()->with('success', 'Matière supprimée avec succès.');
    }

    public function storePlan(SubjectPlanRequest $request)
    {
        SubjectPlan::create($request->validated());

        return back()->with('success', 'Programme pédagogique créé avec succès.');
    }

    public function updatePlan(SubjectPlanRequest $request, SubjectPlan $subjectPlan)
    {
        $subjectPlan->update($request->validated());

        return back()->with('success', 'Programme pédagogique mis à jour avec succès.');
    }

    public function destroyPlan(SubjectPlan $subjectPlan)
    {
        if ($subjectPlan->academicSessions()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce programme : des séances lui sont associées.');
        }

        $subjectPlan->delete();

        return back()->with('success', 'Programme pédagogique supprimé avec succès.');
    }
}
