<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $teachersCount = User::where('role', 'TEACHER')->count();
        $classRoomsCount = ClassRoom::count();
        $roomsCount = Room::count();
        $failedTimetables = Timetable::where('status', 'FAILED')->latest()->take(5)->get();
        $failedCount = Timetable::where('status', 'FAILED')->count();

        return view('dashboard', compact(
            'teachersCount', 
            'classRoomsCount', 
            'roomsCount', 
            'failedCount',
            'failedTimetables'
        ));
    }
}
