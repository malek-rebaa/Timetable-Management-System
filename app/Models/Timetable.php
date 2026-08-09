<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function academicSessions()
    {
        return $this->hasMany(AcademicSession::class);
    }

    public function scopeName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
