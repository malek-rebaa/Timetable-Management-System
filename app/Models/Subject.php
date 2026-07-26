<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function subjectPlans()
    {
        return $this->hasMany(SubjectPlan::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject')->withTimestamps();
    }
}