<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordGenerator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherManagementController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'TEACHER')->get();
        return view('teachers.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
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
        ]);

        $teacher->update($validated);

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
