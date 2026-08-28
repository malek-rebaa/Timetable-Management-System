<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordGenerator;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherManagementController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'TEACHER')->with('subjects')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('teachers.index', compact('teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $email = PasswordGenerator::generateEmail($validated['first_name'], $validated['last_name']);
        $plainPassword = PasswordGenerator::generate();

        $teacher = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $email,
            'phone'      => $validated['phone'] ?? null,
            'password'   => Hash::make($plainPassword),
            'role'       => 'TEACHER',
            'is_active'  => 1,
        ]);
        $teacher->subjects()->sync($validated['subject_ids']);

        return redirect()->route('teachers.index')
            ->with('success', 'Enseignant créé avec succès.')
            ->with('generated_password', $plainPassword)
            ->with('generated_email', $email);
    }

    public function update(Request $request, User $teacher)
    {
        if ($teacher->role !== 'TEACHER') {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'is_active'  => ['required', 'boolean'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $teacher->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'],
        ]);
        $teacher->subjects()->sync($validated['subject_ids']);

        return redirect()->route('teachers.index')
            ->with('success', 'Enseignant mis à jour avec succès.');
    }

    public function destroy(User $teacher)
    {
        if ($teacher->role !== 'TEACHER') {
            abort(403);
        }

        // Check if teacher has active sessions
        if ($teacher->academicSessions()->exists()) {
            return redirect()->route('teachers.index')
                ->with('error', 'Impossible de supprimer cet enseignant car il a des séances associées. Veuillez d\'abord supprimer ses séances.');
        }

        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Enseignant supprimé avec succès.');
    }

    public function resetPassword(User $teacher)
    {
        if ($teacher->role !== 'TEACHER') {
            abort(403);
        }

        $plainPassword = PasswordGenerator::generate();
        $teacher->update(['password' => Hash::make($plainPassword)]);

        return redirect()->route('teachers.index')
            ->with('success', 'Mot de passe réinitialisé avec succès.')
            ->with('generated_password', $plainPassword)
            ->with('generated_email', $teacher->email);
    }
}