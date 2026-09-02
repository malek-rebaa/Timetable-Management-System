<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantModel;

class SubjectPlan extends Model
{
    use HasFactory, TenantModel;

    protected $fillable = [
        'level_id',
        'subject_id',
        'sessions_per_week',
        'session_duration',
        'teaching_type',
    ];

    /**
     * Volume hebdomadaire calculé (minutes) : séances × durée.
     */
    public function getWeeklyHoursAttribute(): int
    {
        return $this->sessions_per_week * $this->session_duration;
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(
            User::class,
            'teacher_subject',
            'subject_id',   // clé pivot côté teacher_subject
            'teacher_id',   // clé pivot vers users
            'subject_id',   // clé locale sur subject_plans (PAS l'id du plan)
            'id'            // clé sur users
        );
    }

    public function academicSessions()
    {
        return $this->hasMany(AcademicSession::class);
    }

    /**
     * Classes du même niveau que ce plan.
     */
    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class, 'level_id', 'level_id');
    }
}
