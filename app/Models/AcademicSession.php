<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_plan_id',
        'teacher_id',
        'class_room_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
        'group_number',
    ];

    public function subjectPlan()
    {
        return $this->belongsTo(SubjectPlan::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}