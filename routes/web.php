<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/logout', function () {
    return redirect()->route('login');
})->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/timetable', function () {
    return view('timetable.index');
})->name('timetable.index');

Route::get('/teachers', function () {
    return view('teachers.index');
})->name('teachers.index');

// Placeholders for sidebar links
Route::get('/levels', function () { return view('dashboard'); })->name('levels.index');
Route::get('/subjects', function () { return view('dashboard'); })->name('subjects.index');
Route::get('/rooms', function () { return view('dashboard'); })->name('rooms.index');
Route::get('/admins', function () { return view('dashboard'); })->name('admins.index');
Route::get('/teacher/timetable', function () { return view('timetable.index'); })->name('teacher.timetable');
