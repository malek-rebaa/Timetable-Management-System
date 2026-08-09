<?php

use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherManagementController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile (own password change) - accessible by all authenticated users
    Route::get('/profile/password', [ProfileController::class, 'showPasswordForm'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Teacher list - accessible by all authenticated users
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [TeacherManagementController::class, 'index'])->name('index');
    });

    // Teacher own timetable
    Route::get('/teacher/timetable', [TimetableController::class, 'index'])->name('teacher.timetable');

    /*
    |--------------------------------------------------------------------------
    | Super Admin Only
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:SUPER_ADMIN'])->group(function () {
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/', [AdminManagementController::class, 'index'])->name('index');
            Route::post('/', [AdminManagementController::class, 'store'])->name('store');
            Route::put('/{admin}', [AdminManagementController::class, 'update'])->name('update');
            Route::put('/{admin}/reset-password', [AdminManagementController::class, 'resetPassword'])->name('reset-password');
            Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Super Admin & Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:SUPER_ADMIN,ADMIN'])->group(function () {
        Route::post('/teachers', [TeacherManagementController::class, 'store'])->name('teachers.store');
        Route::put('/teachers/{teacher}', [TeacherManagementController::class, 'update'])->name('teachers.update');
        Route::put('/teachers/{teacher}/reset-password', [TeacherManagementController::class, 'resetPassword'])->name('teachers.reset-password');
        Route::delete('/teachers/{teacher}', [TeacherManagementController::class, 'destroy'])->name('teachers.destroy');
    });

    // CRUD placeholders (existing)
    Route::prefix('timetable')->name('timetable.')->group(function () {
        Route::get('/', [TimetableController::class, 'index'])->name('index');
        Route::post('/sessions', [TimetableController::class, 'storeSession'])->name('sessions.store');
        Route::put('/sessions/{session}', [TimetableController::class, 'updateSession'])->name('sessions.update');
        Route::delete('/sessions/{session}', [TimetableController::class, 'destroySession'])->name('sessions.destroy');
        Route::post('/sessions/{session}/toggle-lock', [TimetableController::class, 'toggleLock'])->name('sessions.toggle-lock');
        Route::post('/generate', [TimetableController::class, 'generate'])->name('generate');
        Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroyTimetable'])->name('destroy');
    });

    Route::get('/levels', function () {
        return view('levels.index');
    })->name('levels.index');

    Route::get('/subjects', function () {
        return view('subjects.index');
    })->name('subjects.index');

    Route::get('/rooms', function () {
        return view('rooms.index');
    })->name('rooms.index');
});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});
