<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('Les identifiants fournis ne correspondent pas à nos enregistrements.'),
            ]);
        }

        $request->session()->regenerate();
        $this->selectInitialSchool($request, Auth::user());

        return $this->redirectToDashboard();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectToDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        return match ($user->role) {
            'OWNER'       => redirect()->route('schools.index')->with('success', 'Bienvenue Owner'),
            'SUPER_ADMIN' => redirect()->route('dashboard')->with('success', 'Bienvenue Super Admin'),
            'ADMIN'       => redirect()->route('dashboard')->with('success', 'Bienvenue Admin'),
            'TEACHER'     => redirect()->route('teacher.timetable'),
            default       => redirect()->route('login'),
        };
    }

    private function selectInitialSchool(Request $request, User $user): void
    {
        $school = SchoolMembership::query()
            ->with('school')
            ->where('user_id', $user->getKey())
            ->where('status', 'ACTIVE')
            ->whereHas('school', fn ($query) => $query->where('status', 'ACTIVE'))
            ->orderBy('school_id')
            ->first()
            ?->school;

        if ($school !== null) {
            $request->session()->put('active_school_id', $school->getKey());
        }
    }
}
