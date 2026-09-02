<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantModel;

class Subject extends Model
{
    use HasFactory, TenantModel;

    protected $fillable = ['name'];

    public function subjectPlans()
    {
        return $this->hasMany(SubjectPlan::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id')->withTimestamps();
    }
}
