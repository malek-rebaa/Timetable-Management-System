<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantModel;

class AcademicSession extends Model
{
    use HasFactory, TenantModel;

    protected $fillable = [
        'timetable_id',
        'subject_plan_id',
        'teacher_id',
        'class_room_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
        'group_number',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'group_number' => 'integer',
            'is_locked' => 'boolean',
        ];
    }

    /* ------------------------------------------------------------------
       Relations
    ------------------------------------------------------------------ */

    public function timetable()
    {
        return $this->belongsTo(Timetable::class);
    }

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

    /* ------------------------------------------------------------------
       Scopes utiles (liste par ressource, non verrouillées par défaut)
    ------------------------------------------------------------------ */

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForClass(Builder $query, int $classRoomId): Builder
    {
        return $query->where('class_room_id', $classRoomId);
    }

    public function scopeForRoom(Builder $query, int $roomId): Builder
    {
        return $query->where('room_id', $roomId);
    }

    public function scopeForPlan(Builder $query, int $subjectPlanId): Builder
    {
        return $query->where('subject_plan_id', $subjectPlanId);
    }

    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }
}
