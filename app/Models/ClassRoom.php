<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;

    protected $fillable = ['level_id', 'name', 'student_count'];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function academicSessions()
    {
        return $this->hasMany(AcademicSession::class);
    }
}