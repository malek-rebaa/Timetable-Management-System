<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function showPasswordForm()
    {
        return view('profile.password');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();
        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile.password')
            ->with('success', 'Mot de passe modifié avec succès.');
    }
}
