<?php

namespace App\Http\Controllers;

use App\Helpers\PasswordGenerator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'ADMIN')->get();
        return view('admins.index', compact('admins'));
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

        $admin = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $email,
            'phone'      => $validated['phone'] ?? null,
            'password'   => Hash::make($plainPassword),
            'role'       => 'ADMIN',
            'is_active'  => 1,
        ]);

        return redirect()->route('admins.index')
            ->with('success', 'Admin créé avec succès.')
            ->with('generated_password', $plainPassword)
            ->with('generated_email', $email);
    }

    public function update(Request $request, User $admin)
    {
        if ($admin->role !== 'ADMIN') {
            abort(403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
        ]);

        $admin->update($validated);

        return redirect()->route('admins.index')
            ->with('success', 'Admin mis à jour avec succès.');
    }

    public function destroy(User $admin)
    {
        if ($admin->role !== 'ADMIN') {
            abort(403);
        }

        $admin->delete();

        return redirect()->route('admins.index')
            ->with('success', 'Admin supprimé avec succès.');
    }

    public function resetPassword(User $admin)
    {
        if ($admin->role !== 'ADMIN') {
            abort(403);
        }

        $plainPassword = PasswordGenerator::generate();
        $admin->update(['password' => Hash::make($plainPassword)]);

        return redirect()->route('admins.index')
            ->with('success', 'Mot de passe réinitialisé avec succès.')
            ->with('generated_password', $plainPassword)
            ->with('generated_email', $admin->email);
    }
}
