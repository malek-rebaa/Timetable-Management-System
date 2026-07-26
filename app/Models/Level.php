<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function subjectPlans()
    {
        return $this->hasMany(SubjectPlan::class);
    }
}