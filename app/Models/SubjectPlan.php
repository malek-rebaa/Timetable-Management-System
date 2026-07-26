<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'subject_id',
        'weekly_hours',
        'sessions_per_week',
        'session_duration',
        'teaching_type',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicSessions()
    {
        return $this->hasMany(AcademicSession::class);
    }
}